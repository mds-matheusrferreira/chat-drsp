<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\DocumentAuthController;
use App\Http\Controllers\KnowledgeDocumentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ChatController::class, 'index'])->name('chat.index');
Route::get('/chat/health', [ChatController::class, 'health'])->name('chat.health');
Route::post('/chat', [ChatController::class, 'ask'])->name('chat.ask');
Route::post('/chat/stream', [ChatController::class, 'stream'])->name('chat.stream');

Route::get('/documents/login', [DocumentAuthController::class, 'show'])->name('documents.login');
Route::post('/documents/login', [DocumentAuthController::class, 'store'])->name('documents.login.store');
Route::post('/documents/logout', [DocumentAuthController::class, 'destroy'])->name('documents.logout');

Route::middleware('documents.admin')->group(function () {
    Route::get('/documents', [KnowledgeDocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents', [KnowledgeDocumentController::class, 'store'])->name('documents.store');
    Route::post('/documents/text', [KnowledgeDocumentController::class, 'storeText'])->name('documents.text.store');
    Route::post('/documents/delete-selected', [KnowledgeDocumentController::class, 'destroySelected'])->name('documents.destroy-selected');
    Route::delete('/documents/{document}', [KnowledgeDocumentController::class, 'destroy'])->name('documents.destroy');
});
