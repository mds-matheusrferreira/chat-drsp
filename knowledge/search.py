#!/usr/bin/env python3
import argparse
import json
import os
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


def main():
    parser = argparse.ArgumentParser(description='Busca trechos relevantes na base interna DRSP.')
    parser.add_argument('--query', required=True)
    parser.add_argument('--limit', type=int, default=int(os.environ.get('KNOWLEDGE_SEARCH_LIMIT', '5')))
    args = parser.parse_args()

    store = collection()

    if store.count() == 0:
        print(json.dumps({'results': []}, ensure_ascii=False))
        return

    limit = max(1, min(args.limit, 10))

    response = store.query(
        query_texts=[args.query],
        n_results=limit,
        include=['documents', 'metadatas', 'distances'],
    )

    results = []
    documents = response.get('documents', [[]])[0]
    metadatas = response.get('metadatas', [[]])[0]
    distances = response.get('distances', [[]])[0]

    for document, metadata, distance in zip(documents, metadatas, distances):
        results.append({
            'content': document,
            'metadata': metadata or {},
            'distance': distance,
            'title': (metadata or {}).get('title'),
            'original_name': (metadata or {}).get('original_name'),
        })

    print(json.dumps({'results': results}, ensure_ascii=False))


if __name__ == '__main__':
    try:
        main()
    except Exception as error:
        print(json.dumps({
            'results': [],
            'error': str(error),
        }, ensure_ascii=False))
        raise SystemExit(1)
