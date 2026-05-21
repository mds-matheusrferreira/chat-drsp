#!/usr/bin/env python3
import argparse
import json
import os
import re
import unicodedata
from pathlib import Path

import chromadb
from chromadb.utils import embedding_functions


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

    return [term for term in terms if len(term) >= 3]


def lexical_score(query: str, document: str, metadata: dict) -> int:
    normalized_query = normalize_text(query).strip()
    terms = query_terms(query)
    title = normalize_text(metadata.get('title', ''))
    original_name = normalize_text(metadata.get('original_name', ''))
    original_stem = re.sub(r'\.[^.]+$', '', original_name)
    content = normalize_text(document)
    searchable_name = f'{title} {original_name}'
    name_terms = set(re.findall(r'[\w]+', searchable_name, flags=re.UNICODE))
    score = 0

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
    response = store.get(limit=1000, include=['documents', 'metadatas'])
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
