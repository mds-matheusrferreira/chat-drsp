#!/usr/bin/env python3
import argparse
import json
import os
import re
import sys
import unicodedata
from pathlib import Path


def ensure_chromadb_python():
    # PATCH PARA XAMPP/APACHE: Injeta variáveis de sistema necessárias para o ChromaDB/ONNX
    if os.name == 'nt':
        default_user = r"C:\Users\avisala.cebas"
        username = "avisala.cebas"
        if 'USERPROFILE' not in os.environ:
            os.environ['USERPROFILE'] = default_user
        if 'HOME' not in os.environ:
            os.environ['HOME'] = default_user
        if 'USERNAME' not in os.environ:
            os.environ['USERNAME'] = username
        if 'USER' not in os.environ:
            os.environ['USER'] = username

    try:
        import chromadb
        from chromadb.utils import embedding_functions

        return chromadb, embedding_functions
    except ModuleNotFoundError:
        fallback = os.environ.get('KNOWLEDGE_PYTHON_FALLBACK', 'C:/Python314/python.exe')
        current = Path(sys.executable).resolve()
        fallback_path = Path(fallback)

        if os.name == 'nt':
            user_site = 'C:/Users/avisala.cebas/AppData/Roaming/Python/Python314/site-packages'
            os.environ['PYTHONPATH'] = user_site + os.pathsep + os.environ.get('PYTHONPATH', '')

        if os.name == 'nt' and fallback_path.exists() and current != fallback_path.resolve():
            os.execv(str(fallback_path), [str(fallback_path), *sys.argv])

        raise


chromadb, embedding_functions = ensure_chromadb_python()


def project_root() -> Path:
    return Path(__file__).resolve().parents[1]


def chroma_path() -> Path:
    configured = os.environ.get('KNOWLEDGE_CHROMA_PATH')
    if configured:
        path = Path(configured)
        return path if path.is_absolute() else project_root() / path

    return project_root() / 'storage/app/private/knowledge/chromadb'


def collection():
    path = chroma_path()
    path.mkdir(parents=True, exist_ok=True)

    client = chromadb.PersistentClient(path=str(path))
    embedder = embedding_functions.SentenceTransformerEmbeddingFunction(
        model_name=os.environ.get('KNOWLEDGE_EMBEDDING_MODEL', 'all-MiniLM-L6-v2')
    )

    return client.get_or_create_collection(
        name=os.environ.get('KNOWLEDGE_COLLECTION', 'drsp_knowledge'),
        embedding_function=embedder,
    )


def clean_for_json(value):
    if isinstance(value, str):
        return value.encode('utf-8', errors='replace').decode('utf-8')

    if isinstance(value, list):
        return [clean_for_json(item) for item in value]

    if isinstance(value, dict):
        return {clean_for_json(key): clean_for_json(item) for key, item in value.items()}

    return value


def normalize_text(value: str) -> str:
    normalized = unicodedata.normalize('NFKD', value or '')
    without_accents = ''.join(char for char in normalized if not unicodedata.combining(char))

    return without_accents.casefold()


def query_terms(query: str) -> list[str]:
    terms = re.findall(r'[\w]+', normalize_text(query), flags=re.UNICODE)
    # Mudado para >= 2 para não ignorar siglas essenciais do SUAS (Ex: PC, PF, AC)
    return [term for term in terms if len(term) >= 2]


def required_documents_boost(query: str, document: str) -> int:
    normalized_query = normalize_text(query)
    content = normalize_text(document)

    asks_documents = 'document' in normalized_query and any(
        term in normalized_query
        for term in ['necess', 'precis', 'obrigatori', 'exigid', 'requerimento']
    )

    if not asks_documents:
        return 0

    score = 0
    strong_phrases = [
        'acompanhado dos seguintes documentos',
        'acompanhado de documentos',
        'documentos obrigatorios',
        'documentos previstos',
        'requerimento de concessao ou de renovacao da certificacao',
    ]

    for phrase in strong_phrases:
        if phrase in content:
            score += 260

    if 'art. 5' in content and 'requerimento' in content and 'documentos' in content:
        score += 360

    if 'i - declaracao' in content and 'representante legal' in content:
        score += 180

    return score


def lexical_score(query: str, document: str, metadata: dict) -> int:
    normalized_query = normalize_text(query).strip()
    terms = query_terms(query)
    title = normalize_text(metadata.get('title', ''))
    original_name = normalize_text(metadata.get('original_name', ''))
    original_stem = re.sub(r'\.[^.]+$', '', original_name)
    content = normalize_text(document)
    searchable_name = f'{title} {original_name}'
    name_terms = set(re.findall(r'[\w]+', searchable_name, flags=re.UNICODE))
    score = required_documents_boost(query, document)

    if normalized_query and normalized_query in {title, original_stem}:
        score += 1000
    elif normalized_query and normalized_query in searchable_name:
        score += 300

    if normalized_query and normalized_query in content:
        score += 40

    for term in terms:
        if term == title or term == original_stem:
            score += 600
        elif term in name_terms:
            score += 220
        elif term in searchable_name:
            score += 80

        if term in content:
            score += min(content.count(term), 5) * 6

    return score


def lexical_results(store, query: str, limit: int) -> list[dict]:
    # Reduzido o limite preventivo para 400 itens para não travar a CPU do Apache.
    # O ideal a longo prazo é usar ferramentas como Whoosh ou BM25 nativo.
    response = store.get(limit=400, include=['documents', 'metadatas'])
    ids = response.get('ids', [])
    documents = response.get('documents', [])
    metadatas = response.get('metadatas', [])
    matches = []

    for item_id, document, metadata in zip(ids, documents, metadatas):
        metadata = metadata or {}
        score = lexical_score(query, document or '', metadata)

        if score <= 0:
            continue

        matches.append({
            'id': item_id,
            'content': document,
            'metadata': metadata,
            'distance': None,
            'lexical_score': score,
            'title': metadata.get('title'),
            'original_name': metadata.get('original_name'),
        })

    return sorted(
        matches,
        key=lambda item: (
            -item['lexical_score'],
            str(item['metadata'].get('title') or item['metadata'].get('original_name') or ''),
            int(item['metadata'].get('chunk_index') or 0),
        ),
    )[:limit]


def semantic_results(store, query: str, limit: int) -> list[dict]:
    response = store.query(
        query_texts=[query],
        n_results=limit,
        include=['documents', 'metadatas', 'distances'],
    )

    results = []
    ids = response.get('ids', [[]])[0]
    documents = response.get('documents', [[]])[0]
    metadatas = response.get('metadatas', [[]])[0]
    distances = response.get('distances', [[]])[0]

    for item_id, document, metadata, distance in zip(ids, documents, metadatas, distances):
        metadata = metadata or {}
        results.append({
            'id': item_id,
            'content': document,
            'metadata': metadata,
            'distance': distance,
            'lexical_score': lexical_score(query, document or '', metadata),
            'title': metadata.get('title'),
            'original_name': metadata.get('original_name'),
        })

    return results


def merge_results(lexical: list[dict], semantic: list[dict], limit: int) -> list[dict]:
    merged = []
    seen = set()

    for result in lexical + semantic:
        item_id = result.get('id')

        if item_id in seen:
            continue

        seen.add(item_id)
        merged.append(result)

        if len(merged) >= limit:
            break

    return merged


def main():
    parser = argparse.ArgumentParser(description='Busca trechos relevantes na base interna DRSP.')
    parser.add_argument('--query', required=True)
    parser.add_argument('--limit', type=int, default=int(os.environ.get('KNOWLEDGE_SEARCH_LIMIT', '8')))
    args = parser.parse_args()

    store = collection()

    if store.count() == 0:
        print(json.dumps({'results': []}))
        return

    limit = max(1, min(args.limit, 20))
    results = merge_results(
        lexical_results(store, args.query, limit),
        semantic_results(store, args.query, limit),
        limit,
    )

    print(json.dumps(clean_for_json({'results': results})))


if __name__ == '__main__':
    try:
        main()
    except Exception as error:
        print(json.dumps(clean_for_json({
            'results': [],
            'error': str(error),
        })))
        raise SystemExit(1)