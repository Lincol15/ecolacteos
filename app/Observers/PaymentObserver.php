<?php

namespace App\Observers;

use App\Models\Payment;

class PaymentObserver
{
    /**
     * Handle the Payment "creating" event.
     */
    public function creating(Payment $payment): void
    {
        // Validar que las fechas del periodo sean lógicas
        if ($payment->period_start && $payment->period_end) {
            if ($payment->period_start > $payment->period_end) {
                throw new \Exception('La fecha de inicio del periodo no puede ser posterior a la fecha de fin');
            }
        }

        // Validar que el monto total sea positivo
        if ($payment->total_amount < 0) {
            throw new \Exception('El monto total del pago no puede ser negativo');
        }

        // Calcular total_amount si no está establecido
        if (! isset($payment->total_amount) || $payment->total_amount === 0) {
            $payment->total_amount = round(
                $payment->base_amount
                + $payment->quality_bonus
                + $payment->production_bonus
                - $payment->deductions
                - ($payment->llevado_a_planta ?? 0),
                2
            );
        }
    }

    /**
     * Handle the Payment "updating" event.
     */
    public function updating(Payment $payment): void
    {
        // Recalcular total si cambian los componentes
        if ($payment->isDirty(['base_amount', 'quality_bonus', 'production_bonus', 'deductions', 'llevado_a_planta'])) {
            $payment->total_amount = round(
                $payment->base_amount
                + $payment->quality_bonus
                + $payment->production_bonus
                - $payment->deductions
                - ($payment->llevado_a_planta ?? 0),
                2
            );
        }

        // Validar que no se pueda cambiar el estado de 'pagado' a otro estado
        if ($payment->isDirty('status') && $payment->getOriginal('status') === 'pagado') {
            if ($payment->status !== 'pagado') {
                throw new \Exception('No se puede revertir un pago que ya fue procesado');
            }
        }
    }

    /**
     * Handle the Payment "deleting" event.
     */
    public function deleting(Payment $payment): void
    {
        // No permitir eliminar pagos que ya fueron procesados
        if ($payment->status === 'pagado') {
            throw new \Exception('No se puede eliminar un pago que ya fue procesado');
        }
    }

    /**
     * Handle the Payment "restored" event.
     */
    public function restored(Payment $payment): void
    {
        // Validar que se pueda restaurar
        if ($payment->status === 'pagado') {
            \Log::warning('Se restauró un pago que estaba pagado', ['payment_id' => $payment->id]);
        }
    }
}
