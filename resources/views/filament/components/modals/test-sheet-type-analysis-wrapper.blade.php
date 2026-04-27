<div class="modal-content-type-analysis">
    @livewire(\App\Livewire\TestSheetTypeAnalysis::class, [
        'testsheet' => $record,
    ], key('type-analysis-' . $record->id))
</div>
