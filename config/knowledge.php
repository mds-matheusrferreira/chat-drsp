<?php

$chunkSize = max(200, min((int) env('KNOWLEDGE_CHUNK_SIZE', 700), 3000));

return [
    'python_bin' => env('KNOWLEDGE_PYTHON_BIN', 'python3'),
    'document_path' => env('KNOWLEDGE_DOCUMENT_PATH', 'knowledge/documents'),
    'chroma_path' => env('KNOWLEDGE_CHROMA_PATH', storage_path('app/private/knowledge/chromadb')),
    'tmp_path' => env('KNOWLEDGE_TMP_PATH', storage_path('app/private/knowledge/tmp')),
    'search_limit' => max(1, min((int) env('KNOWLEDGE_SEARCH_LIMIT', 8), 20)),
    'chunk_size' => $chunkSize,
    'chunk_overlap' => min(max(0, min((int) env('KNOWLEDGE_CHUNK_OVERLAP', 120), 1000)), $chunkSize - 1),
    'max_upload_mb' => (int) env('KNOWLEDGE_MAX_UPLOAD_MB', 50),
    'allowed_extensions' => ['txt', 'csv', 'pdf', 'docx', 'xlsx', 'xls'],
    'document_admin_username' => env('KNOWLEDGE_DOCUMENT_ADMIN_USERNAME', 'admin'),
    'document_admin_password' => env('KNOWLEDGE_DOCUMENT_ADMIN_PASSWORD', 'drsp'),
];
