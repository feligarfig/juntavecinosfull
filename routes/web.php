Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/finanzas', [FinanzasController::class, 'index'])->name('finanzas.index');
    Route::post('/finanzas', [FinanzasController::class, 'store'])->name('finanzas.store');
});