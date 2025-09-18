<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        // 1) Coletamos os filtros vindos via query string (?action=...&model_type=... etc.)
        //    Assim conseguimos repassar para a view e manter os "values" preenchidos.
        $filters = $request->only([
            'action',        // string ex: created, updated, permission-detached...
            'model_type',    // FQCN ex: App\Models\Registro
            'model_id',      // inteiro
            'user_id',       // inteiro
            'date_from',     // YYYY-MM-DD
            'date_to',       // YYYY-MM-DD
            'q',             // busca textual na descrição
        ]);

        // 2) Base da consulta.
        //    with('user:id,name') evita N+1 e traz só o necessário da relação user.
        $query = ActivityLog::query()
            ->with('user:id,name')
            ->latest('id'); // ordena por id desc (poderia ser created_at também)

        // 3) Aplica filtros condicionalmente usando ->when()
        //    Docs: "Conditionally Building Queries" https://laravel.com/docs/12.x/queries#conditionally-building-queries

        // Filtra por ação exata (ex.: created / deleted / role-attached)
        $query->when($filters['action'] ?? null, function ($q, $action) {
            $q->where('action', $action);
        });

        // Filtra por model_type (FQCN ex.: App\Models\User)
        $query->when($filters['model_type'] ?? null, function ($q, $type) {
            $q->where('model_type', $type);
        });

        // Filtra por model_id (inteiro)
        $query->when($filters['model_id'] ?? null, function ($q, $id) {
            $q->where('model_id', (int) $id);
        });

        // Filtra por usuário causador (se você popula user_id ao logar)
        $query->when($filters['user_id'] ?? null, function ($q, $userId) {
            $q->where('user_id', (int) $userId);
        });

        // Intervalo de datas (created_at)
        // Aceita from/to opcionais. Constrói between quando houver ambos.
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo   = $filters['date_to']   ?? null;

        if ($dateFrom && $dateTo) {
            // whereBetween em created_at, expandindo para o dia inteiro
            // Docs whereBetween: https://laravel.com/docs/12.x/queries#additional-where-clauses
            $query->whereBetween('created_at', [
                $dateFrom . ' 00:00:00',
                $dateTo   . ' 23:59:59',
            ]);
        } else {
            // Apenas "a partir de"
            $query->when($dateFrom, fn ($q, $from) => $q->where('created_at', '>=', $from . ' 00:00:00'));
            // Apenas "até"
            $query->when($dateTo,   fn ($q, $to)   => $q->where('created_at', '<=', $to . ' 23:59:59'));
        }

        // Busca textual simples em "description" (LIKE %q%)
        $query->when($filters['q'] ?? null, function ($q, $term) {
            $q->where('description', 'like', '%' . str_replace('%', '\\%', $term) . '%');
        });

        // 4) Paginação (Tailwind-ready)
        //    Docs: https://laravel.com/docs/12.x/pagination#displaying-pagination-results
        $logs = $query->paginate(20)->withQueryString(); // preserva os filtros ao paginar

        // 5) Opções para os <select>s (distintos do próprio banco)
        $actions     = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');
        $modelTypes  = ActivityLog::select('model_type')->distinct()->orderBy('model_type')->pluck('model_type');
        $users       = User::select('id', 'name')->orderBy('name')->get();

        // 6) Renderiza a view com tudo que precisamos
        return view('admin.log.index', compact('logs', 'filters', 'actions', 'modelTypes', 'users'));
    }
}
