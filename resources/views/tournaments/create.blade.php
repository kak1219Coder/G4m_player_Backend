@extends('layouts.app')
@section('title', 'Créer une Compétition')
@section('pageTitle')
<h1>Créer une Compétition</h1>
<nav>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('welcome_admin') }}">Tableau de Board</a></li>
        <li class="breadcrumb-item"><a href="{{ route('competitions.index') }}">Compétitions</a></li>
        <li class="breadcrumb-item active">Créer</li>
    </ol>
</nav>
@endsection

@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="card shadow-sm border-0 rounded-4">
    <div class="card-body">
        <form action="{{ route('competitions.store') }}" method="POST" id="competitionForm">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="game_id" class="form-label">Jeu <span class="text-danger">*</span></label>
                    <select name="game_id" id="game_id" class="form-control" required>
                        <option value="">Sélectionner</option>
                        @foreach($games as $game)
                            <option value="{{ $game->id }}" {{ old('game_id') == $game->id ? 'selected' : '' }}>{{ $game->name }}</option>
                        @endforeach
                    </select>
                    @error('game_id')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="title" class="form-label">Titre <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" required>
                    @error('title')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="start_date" class="form-label">Date & Heure de début <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="start_date" id="start_date" class="form-control" value="{{ old('start_date') }}" required>
                    @error('start_date')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="end_date" class="form-label">Date & Heure de fin <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="end_date" id="end_date" class="form-control" value="{{ old('end_date') }}" required>
                    @error('end_date')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label for="mode" class="form-label">Mode <span class="text-danger">*</span></label>
                    <select name="mode" id="mode" class="form-control" required>
                        <option value="">--</option>
                        <option value="solo" {{ old('mode') == 'solo' ? 'selected' : '' }}>Solo</option>
                        <option value="duo" {{ old('mode') == 'duo' ? 'selected' : '' }}>Duo</option>
                        <option value="squad" {{ old('mode') == 'squad' ? 'selected' : '' }}>Squad</option>
                    </select>
                    @error('mode')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                    @enderror
                </div> 
                <div class="col-md-3 mb-3">
                    <label for="is_online" class="form-label">En ligne ? <span class="text-danger">*</span></label>
                    <select name="is_online" id="is_online" class="form-control" required>
                        <option value="1" {{ old('is_online') == '1' ? 'selected' : '' }}>Oui</option>
                        <option value="0" {{ old('is_online') == '0' ? 'selected' : '' }}>Non</option>
                    </select>
                    @error('is_online')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label for="location" class="form-label">Lieu (si hors ligne)</label>
                    <input type="text" name="location" id="location" class="form-control" value="{{ old('location') }}">
                    @error('location')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label for="reward" class="form-label">Récompense <span class="text-danger">*</span></label>
                    <input type="text" name="reward" id="reward" class="form-control" value="{{ old('reward') }}">
                    @error('reward')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label for="max_participants" class="form-label">Nombre de Participants <span class="text-danger">*</span></label>
                    <input type="number" name="max_participants" id="max_participants" class="form-control" value="{{ old('max_participants') }}" >
                    @error('max_participants')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Règles <span class="text-danger">*</span></label>
                    <div id="rules-list">
                        <div class="input-group mb-2">
                            <input type="text" name="rules[]" class="form-control" placeholder="Règle 1">
                            <button type="button" class="btn btn-outline-secondary add-rule">+</button>
                        </div>
                        @if($errors->has('rules'))
                            <div class="alert alert-danger mt-2">{{ $errors->first('rules') }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Contacts</label>
                    <div id="contacts-list">
                        <div class="input-group mb-2">
                            <input type="text" name="contact_link[0][type]" class="form-control" placeholder="Type (ex: Discord)">
                            <input type="text" name="contact_link[0][url]" class="form-control" placeholder="Lien">
                            <button type="button" class="btn btn-outline-secondary add-contact">+</button>
                        </div>
                        @if($errors->has('contact_link'))
                            <div class="alert alert-danger mt-2">{{ $errors->first('contact_link') }}</div>
                        @endif
                    </div>
                </div>
            </div>
            <button type="submit" id="submitBtn" class="btn btn-success">
                <span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                Enregistrer la Compétition
            </button>
        </form>
    </div>
</div>
<script>
    let contactIndex = 1;
    let ruleIndex = 1;

    document.addEventListener('click', function(e) {
        // Contacts dynamique
        if (e.target.classList.contains('add-contact')) {
            e.preventDefault();
            const contactsList = document.getElementById('contacts-list');
            const div = document.createElement('div');
            div.className = 'input-group mb-2';
            div.innerHTML = `
                <input type="text" name="contact_link[${contactIndex}][type]" class="form-control" placeholder="Type (ex: Discord)">
                <input type="text" name="contact_link[${contactIndex}][url]" class="form-control" placeholder="Lien">
                <button type="button" class="btn btn-outline-danger remove-contact">-</button>
            `;
            contactsList.appendChild(div);
            contactIndex++;
        }
        if (e.target.classList.contains('remove-contact')) {
            e.preventDefault();
            e.target.closest('.input-group').remove();
        }

        // Règles dynamique
        if (e.target.classList.contains('add-rule')) {
            e.preventDefault();
            const rulesList = document.getElementById('rules-list');
            const div = document.createElement('div');
            div.className = 'input-group mb-2';
            div.innerHTML = `
                <input type="text" name="rules[]" class="form-control" placeholder="Règle ${ruleIndex + 1}">
                <button type="button" class="btn btn-outline-danger remove-rule">-</button>
            `;
            rulesList.appendChild(div);
            ruleIndex++;
        }
        if (e.target.classList.contains('remove-rule')) {
            e.preventDefault();
            e.target.closest('.input-group').remove();
        }
    });

    document.getElementById('competitionForm').addEventListener('submit', function() {
        document.getElementById('spinner').classList.remove('d-none');
        document.getElementById('submitBtn').setAttribute('disabled', 'disabled');
    });
</script>
@endsection