<?php

return [
    'python_bin' => env('KNOWLEDGE_PYTHON_BIN', 'python3'),
    'document_path' => env('KNOWLEDGE_DOCUMENT_PATH', 'knowledge/documents'),
    'chroma_path' => env('KNOWLEDGE_CHROMA_PATH', storage_path('app/private/knowledge/chromadb')),
    'search_limit' => max(1, min((int) env('KNOWLEDGE_SEARCH_LIMIT', 5), 10)),
    'max_upload_mb' => (int) env('KNOWLEDGE_MAX_UPLOAD_MB', 50),
    'allowed_extensions' => ['txt', 'csv', 'pdf', 'docx', 'xlsx', 'xls'],
    'document_admin_username' => env('KNOWLEDGE_DOCUMENT_ADMIN_USERNAME', 'admin'),
    'document_admin_password' => env('KNOWLEDGE_DOCUMENT_ADMIN_PASSWORD', 'drsp'),
];
