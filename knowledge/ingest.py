#!/usr/bin/env python3
import argparse
import csv
import json
import os
from pathlib import Path

import chromadb
from chromadb.utils import embedding_functions

try:
    import pandas as pd
except Exception:
    pd = None

try:
    from docx import Document as DocxDocument
except Exception:
    DocxDocument = None

try:
    from openpyxl import load_workbook
except Exception:
    load_workbook = None

try:
    from pypdf import PdfReader
except Exception:
    PdfReader = None


SUPPORTED_EXTENSIONS = {'.txt', '.csv', '.pdf', '.docx', '.xlsx', '.xls'}


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
        metadata={'description': 'Base interna DRSP/SUAS'},
    )


def extract_text(path: Path) -> str:
    extension = path.suffix.lower()

    if extension not in SUPPORTED_EXTENSIONS:
        raise ValueError(f'Tipo de arquivo não suportado: {extension}')

    if extension == '.txt':
        return path.read_text(encoding='utf-8', errors='ignore')

    if extension == '.csv':
        rows = []
        with path.open('r', encoding='utf-8', errors='ignore', newline='') as file:
            reader = csv.reader(file)
            for row in reader:
                rows.append(' | '.join(cell.strip() for cell in row if cell is not None))
        return '\n'.join(rows)

    if extension == '.pdf':
        if PdfReader is None:
            raise RuntimeError('Dependência pypdf não instalada.')

        reader = PdfReader(str(path))
        pages = []
        for index, page in enumerate(reader.pages, start=1):
            text = page.extract_text() or ''
            if text.strip():
                pages.append(f'[Página {index}]\n{text}')
        return '\n\n'.join(pages)

    if extension == '.docx':
        if DocxDocument is None:
            raise RuntimeError('Dependência python-docx não instalada.')

        document = DocxDocument(str(path))
        parts = [paragraph.text for paragraph in document.paragraphs if paragraph.text.strip()]

        for table in document.tables:
            for row in table.rows:
                values = [cell.text.strip() for cell in row.cells if cell.text.strip()]
                if values:
                    parts.append(' | '.join(values))

        return '\n'.join(parts)

    if extension in {'.xlsx', '.xls'}:
        if extension == '.xlsx' and load_workbook is not None:
            workbook = load_workbook(filename=str(path), read_only=True, data_only=True)
            lines = []
            for sheet in workbook.worksheets:
                lines.append(f'[Aba: {sheet.title}]')
                for row in sheet.iter_rows(values_only=True):
                    values = [str(value).strip() for value in row if value is not None and str(value).strip()]
                    if values:
                        lines.append(' | '.join(values))
            return '\n'.join(lines)

        if pd is None:
            raise RuntimeError('Dependência pandas/openpyxl não instalada.')

        sheets = pd.read_excel(str(path), sheet_name=None)
        lines = []
        for sheet_name, frame in sheets.items():
            lines.append(f'[Aba: {sheet_name}]')
            lines.append(frame.fillna('').to_csv(index=False, sep='|'))
        return '\n'.join(lines)

    return ''


def chunk_text(text: str, chunk_size: int = 1200, overlap: int = 180) -> list[str]:
    normalized = '\n'.join(line.strip() for line in text.splitlines())
    normalized = '\n'.join(line for line in normalized.splitlines() if line)

    if not normalized:
        return []

    chunks = []
    start = 0

    while start < len(normalized):
        end = min(start + chunk_size, len(normalized))
        chunk = normalized[start:end].strip()

        if chunk:
            chunks.append(chunk)

        if end == len(normalized):
            break

        start = max(0, end - overlap)

    return chunks


def main():
    parser = argparse.ArgumentParser(description='Indexa documento interno DRSP no ChromaDB.')
    parser.add_argument('--document-id', required=True)
    parser.add_argument('--path', required=True)
    parser.add_argument('--title', required=True)
    parser.add_argument('--original-name', default='')
    args = parser.parse_args()

    file_path = Path(args.path)

    if not file_path.exists():
        raise FileNotFoundError(f'Arquivo não encontrado: {file_path}')

    text = extract_text(file_path)
    chunks = chunk_text(text)

    if not chunks:
        raise ValueError('Não foi possível extrair texto útil do documento.')

    ids = [f'doc-{args.document_id}-chunk-{index}' for index in range(len(chunks))]
    metadatas = [
        {
            'document_id': str(args.document_id),
            'title': args.title,
            'original_name': args.original_name,
            'chunk_index': index,
            'extension': file_path.suffix.lower().lstrip('.'),
        }
        for index in range(len(chunks))
    ]

    store = collection()
    store.delete(where={'document_id': str(args.document_id)})
    store.add(ids=ids, documents=chunks, metadatas=metadatas)

    print(json.dumps({
        'status': 'ready',
        'chunks_count': len(chunks),
        'characters_count': len(text),
    }, ensure_ascii=False))


if __name__ == '__main__':
    try:
        main()
    except Exception as error:
        print(json.dumps({
            'status': 'failed',
            'error': str(error),
        }, ensure_ascii=False))
        raise SystemExit(1)
