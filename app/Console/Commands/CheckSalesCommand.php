<?php

namespace App\Console\Commands;

use App\Models\Sale;
use App\Models\User;
use App\Notifications\BasicNotification;
use Illuminate\Console\Command;

class CheckSalesCommand extends Command
{
    protected $signature = 'sales:check-unpaid-limit-dates';
    protected $description = 'Check sales with limit_date and unpaid invoices';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Buscar ventas con limit_date igual a hoy y que no estén pagadas
        $sales = Sale::whereDate('limit_date', today())
            ->whereNull('paid_at')
            ->get();

        // Notificar al usuario
        foreach ($sales as $sale) {
            $folio = 'V-' . str_pad($sale->id, 4, "0", STR_PAD_LEFT);
            $title = "Plazo de crédito vencido";
            $description = "Ha vencido el plazo para liquidar la venta <span class='text-primary'>$folio</span> del cliente <span class='text-primary'>{$sale->client->name}</span>";
            $url = route('sales.show', $sale);

            $user = User::first();
            $user->notify(new BasicNotification($title, $description, $url));
            $this->info("Se ha notificado al usuario {$user->name} sobre la venta con ID {$sale->id}");
        }

        $this->info('La verificación de ventas se ha completado.');
    }
}
