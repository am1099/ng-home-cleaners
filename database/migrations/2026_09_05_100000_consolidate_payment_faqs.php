<?php

use App\Models\Faq;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Faq::query()
            ->where('question', 'How do I pay?')
            ->delete();

        $payment = Faq::query()->where('question', 'How and when do I pay?')->first();

        if ($payment) {
            $payment->update([
                'answer' => 'Pay in full by card before the clean, or pay a 50% deposit to secure the slot and the balance on the day. Regular weekly and fortnightly customers skip the deposit and are invoiced per visit. No cash needed, no subscription, and the total is the fixed written price you agreed - we do not add to it afterwards.',
            ]);
        }
    }

    public function down(): void
    {
        // Irreversible content consolidation.
    }
};
