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
    parser = argparse.ArgumentParser(description='Remove documento interno DRSP do ChromaDB.')
    parser.add_argument('--document-id', required=True)
    args = parser.parse_args()

    store = collection()
    store.delete(where={'document_id': str(args.document_id)})

    print(json.dumps({'status': 'deleted'}, ensure_ascii=False))


if __name__ == '__main__':
    try:
        main()
    except Exception as error:
        print(json.dumps({
            'status': 'failed',
            'error': str(error),
        }, ensure_ascii=False))
        raise SystemExit(1)
