@extends('layouts.app')
@section('title', 'Liste des Compétitions')
@section('pageTitle')
<h1>Liste des Compétitions</h1>
<nav>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('welcome_admin') }}">Tableau de Board</a></li>
        <li class="breadcrumb-item active">Compétitions</li>
    </ol>
</nav>
@endsection

@section('content')
@include('layouts.messages_flash')
<div class="card shadow-sm border-0 rounded-4">
    <div class="card-body">
        <h5 class="card-title">Compétitions</h5>
        <div class="mb-3">
            <a href="{{ route('competitions.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Ajouter une Compétition
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover datatable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Jeu</th>
                        <th>Titre</th>
                        <th>Période</th>
                        <th>Mode</th>
                        <th>Statut</th>
                        <th>Lieu</th>
                        <th>Récompense</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($competitions as $competition)
                        <tr>
                            <td>{{ $competition->id }}</td>
                            <td>{{ $competition->game?->name }}</td>
                            <td>{{ $competition->title }}</td>
                            <td>
                                @if($competition->start_date && $competition->end_date)
                                    {{ \Carbon\Carbon::parse($competition->start_date)->format('d/m/Y H:i') }}
                                    &rarr;
                                    {{ \Carbon\Carbon::parse($competition->end_date)->format('d/m/Y H:i') }}
                                @elseif($competition->start_date)
                                    {{ \Carbon\Carbon::parse($competition->start_date)->format('d/m/Y H:i') }}
                                @endif
                            </td>
                            <td>
                                @if($competition->mode)
                                    <span class="badge bg-secondary">{{ ucfirst($competition->mode) }}</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'upcoming' => 'info',
                                        'ongoing' => 'warning',
                                        'completed' => 'success',
                                        'cancel' => 'danger'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$competition->status] ?? 'secondary' }}">
                                    {{ ucfirst($competition->status) }}
                                </span>
                            </td>
                            <td>
                                @if(!$competition->is_online)
                                    <span class="text-primary">{{ $competition->location }}</span>
                                @else
                                    <span class="text-success">En ligne</span>
                                @endif
                            </td>
                            <td>{{ $competition->reward }}</td>
                            <td>
                                <a href="{{ route('competitions.show', $competition->id) }}" class="btn btn-sm btn-outline-primary">Voir</a>
                                {{-- <a href="#" class="btn btn-sm btn-outline-secondary">Modifier</a> --}}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Si tu utilises DataTables JS -->
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.$ && $.fn.DataTable) {
            $('.datatable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
                }
            });
        }
    });
</script>
@endpush
@endsection