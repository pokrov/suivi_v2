@php
  $pp = $pp ?? null;
@endphp

<div class="row g-3">
  <div class="col-md-3">
    <label class="form-label">N° dossier *</label>
    <input name="numero_dossier" class="form-control" required
           value="{{ old('numero_dossier', $pp->numero_dossier ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Province</label>
    <input name="province" class="form-control"
           value="{{ old('province', $pp->province ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Commune 1</label>
    <input name="commune_1" class="form-control"
           value="{{ old('commune_1', $pp->commune_1 ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Commune 2</label>
    <input name="commune_2" class="form-control"
           value="{{ old('commune_2', $pp->commune_2 ?? '') }}">
  </div>

  <div class="col-md-3">
    <label class="form-label">Date d'arrivée</label>
    <input type="date" name="date_arrivee" class="form-control"
           value="{{ old('date_arrivee', optional($pp?->date_arrivee)->format('Y-m-d')) }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">N° arrivée</label>
    <input name="numero_arrivee" class="form-control"
           value="{{ old('numero_arrivee', $pp->numero_arrivee ?? '') }}">
  </div>

  <div class="col-md-4">
    <label class="form-label">Pétitionnaire</label>
    <input name="petitionnaire" class="form-control"
           value="{{ old('petitionnaire', $pp->petitionnaire ?? '') }}">
  </div>
  <div class="col-md-2">
    <label class="form-label">A propriétaire ?</label>
    <select name="a_proprietaire" class="form-select">
      <option value="0" @selected(old('a_proprietaire', (int)($pp->a_proprietaire ?? 0))===0)>Non</option>
      <option value="1" @selected(old('a_proprietaire', (int)($pp->a_proprietaire ?? 0))===1)>Oui</option>
    </select>
  </div>
  <div class="col-md-4">
    <label class="form-label">Propriétaire</label>
    <input name="proprietaire" class="form-control"
           value="{{ old('proprietaire', $pp->proprietaire ?? '') }}">
  </div>
  <div class="col-md-2">
    <label class="form-label">Cat. pétitionnaire</label>
    <input name="categorie_petitionnaire" class="form-control"
           value="{{ old('categorie_petitionnaire', $pp->categorie_petitionnaire ?? '') }}">
  </div>

  <div class="col-md-6">
    <label class="form-label">Maître d'œuvre</label>
    <input name="maitre_oeuvre" class="form-control"
           value="{{ old('maitre_oeuvre', $pp->maitre_oeuvre ?? '') }}">
  </div>
  <div class="col-md-6">
    <label class="form-label">Intitulé du projet</label>
    <input name="intitule_projet" class="form-control"
           value="{{ old('intitule_projet', $pp->intitule_projet ?? '') }}">
  </div>

  <div class="col-md-4">
    <label class="form-label">Catégorie du projet</label>
    @php $cats = old('categorie_projet', $pp->categorie_projet ?? []); @endphp
    <input type="text" class="form-control" placeholder="ex: Habitation, Commerce"
           value="{{ is_array($cats) ? implode(', ', $cats) : $cats }}"
           oninput="document.getElementById('cats_json').value = JSON.stringify(this.value.split(',').map(s=>s.trim()).filter(Boolean))">
    <input type="hidden" id="cats_json" name="categorie_projet"
           value='@json($cats)'>
    <small class="text-muted">Sépare par virgules — stocké en JSON.</small>
  </div>
  <div class="col-md-4">
    <label class="form-label">Contexte</label>
    <input name="contexte_projet" class="form-control"
           value="{{ old('contexte_projet', $pp->contexte_projet ?? '') }}">
  </div>
  <div class="col-md-4">
    <label class="form-label">Référence foncière</label>
    <input name="reference_fonciere" class="form-control"
           value="{{ old('reference_fonciere', $pp->reference_fonciere ?? '') }}">
  </div>

  <div class="col-md-8">
    <label class="form-label">Situation (adresse)</label>
    <input name="situation" class="form-control"
           value="{{ old('situation', $pp->situation ?? '') }}">
  </div>
  <div class="col-md-4">
    <label class="form-label">Lien GED</label>
    <input name="lien_ged" class="form-control" placeholder="https://..."
           value="{{ old('lien_ged', $pp->lien_ged ?? '') }}">
  </div>

  <div class="col-md-3">
    <label class="form-label">Superficie terrain (m²)</label>
    <input type="number" step="0.01" name="superficie_terrain" class="form-control"
           value="{{ old('superficie_terrain', $pp->superficie_terrain ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Superficie couverte (m²)</label>
    <input type="number" step="0.01" name="superficie_couverte" class="form-control"
           value="{{ old('superficie_couverte', $pp->superficie_couverte ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Investissement (MAD)</label>
    <input type="number" step="0.01" name="montant_investissement" class="form-control"
           value="{{ old('montant_investissement', $pp->montant_investissement ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Logements</label>
    <input type="number" name="nb_logements" class="form-control"
           value="{{ old('nb_logements', $pp->nb_logements ?? '') }}">
  </div>

  <div class="col-md-3">
    <label class="form-label">N° Rokhas</label>
    <input name="rokhas_numero" class="form-control"
           value="{{ old('rokhas_numero', $pp->rokhas_numero ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Lien Rokhas</label>
    <input name="rokhas_lien" class="form-control" placeholder="https://..."
           value="{{ old('rokhas_lien', $pp->rokhas_lien ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Avis Rokhas</label>
    <select name="rokhas_avis" class="form-select">
      @php $avis = old('rokhas_avis', $pp->rokhas_avis ?? ''); @endphp
      <option value="">—</option>
      <option value="favorable"    @selected($avis==='favorable')>Favorable</option>
      <option value="defavorable"  @selected($avis==='defavorable')>Défavorable</option>
      <option value="sous_reserve" @selected($avis==='sous_reserve')>Sous réserve</option>
      <option value="sans_objet"   @selected($avis==='sans_objet')>Sans objet</option>
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label">Date avis</label>
    <input type="date" name="rokhas_avis_date" class="form-control"
           value="{{ old('rokhas_avis_date', optional($pp?->rokhas_avis_date)->format('Y-m-d')) }}">
  </div>

  <div class="col-md-6">
    <label class="form-label">Pièce (URL PDF)</label>
    <input name="rokhas_piece_url" class="form-control" placeholder="https://..."
           value="{{ old('rokhas_piece_url', $pp->rokhas_piece_url ?? '') }}">
  </div>
  <div class="col-md-6">
    <label class="form-label">Commentaire (avis Rokhas)</label>
    <input name="rokhas_avis_commentaire" class="form-control"
           value="{{ old('rokhas_avis_commentaire', $pp->rokhas_avis_commentaire ?? '') }}">
  </div>

  <div class="col-12">
    <label class="form-label">Observations</label>
    <textarea name="observations" rows="3" class="form-control">{{ old('observations', $pp->observations ?? '') }}</textarea>
  </div>

  @if($pp)
    <div class="col-md-3">
      <label class="form-label">État</label>
      <select name="etat" class="form-select">
        @php $etat = old('etat', $pp->etat ?? 'enregistrement'); @endphp
        <option value="enregistrement" @selected($etat==='enregistrement')>Enregistrement</option>
        <option value="archive"        @selected($etat==='archive')>Archivé</option>
      </select>
    </div>
  @endif

  @if ($errors->any())
    <div class="col-12">
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    </div>
  @endif
</div>
