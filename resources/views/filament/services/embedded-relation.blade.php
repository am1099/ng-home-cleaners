@livewire($component, [
    'ownerRecord' => $ownerRecord,
    'pageClass' => $pageClass,
], key($component.'-'.$ownerRecord->getKey()))
