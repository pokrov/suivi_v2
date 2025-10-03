{{-- resources/views/grandprojets/cpc/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
  <div class="card shadow">
    <div class="card-header bg-success text-white d-flex align-items-center justify-content-between">
      <h2 class="mb-0">Enregistrement d'un Grand Projet - CPC</h2>
      <small class="opacity-75">Étape : Saisie initiale</small>
    </div>
    
    <div class="card-body">
      {{-- Bannière duplication --}}
      <div id="dupBanner" class="alert alert-warning d-none">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <strong>⚠️ Dossier existant.</strong>
            <span id="dupMsg">Vous préparez le <span class="fw-bold" id="examNo">2ᵉ</span> examen.</span>
            <span id="dupAvisWrap" class="ms-2 small text-muted d-none">Dernier avis : <span id="dupAvis"></span>.</span>
          </div>
          <a id="openExistingLink" href="#" target="_blank" class="btn btn-sm btn-outline-secondary">Ouvrir la fiche</a>
        </div>
      </div>

      {{-- Erreurs serveur --}}
      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
      @endif

      {{-- Choix de la route selon rôle --}}
      @if(Auth::user()->hasRole('chef'))
        <form id="cpcForm" action="{{ route('chef.grandprojets.cpc.store') }}" method="POST" novalidate>
      @elseif(Auth::user()->hasRole('saisie_cpc'))
        <form id="cpcForm" action="{{ route('saisie_cpc.cpc.store') }}" method="POST" novalidate>
      @endif
        @csrf

        <div class="row">
          {{-- ================= Colonne gauche ================= --}}
          <div class="col-md-6">
            <h5 class="mb-3 text-secondary border-bottom pb-2">Identification & Localisation</h5>

            {{-- Numéro de dossier --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Numéro de Dossier <span class="text-danger">*</span></label>
              <input type="text" name="numero_dossier"
                     class="form-control @error('numero_dossier') is-invalid @enderror"
                     value="{{ old('numero_dossier', $grandProjet->numero_dossier ?? '') }}"
                     placeholder="ex: 1234/25"
                     pattern="\d+/\d{2}"
                     title="Format: nombre/AA, ex: 452/25" required>
              <div class="invalid-feedback">Format attendu : nombre/XX (ex. 452/25)</div>
            </div>

            {{-- Province --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Préfecture / Province <span class="text-danger">*</span></label>
              <select name="province" id="provinceSelect" class="form-select"
                      data-validate='{"required":true,"message":"Sélectionnez une province"}'>
                <option value="">-- Sélectionner --</option>
                @foreach([
                  'Préfecture Oujda-Angad','Province Berkane',
                  'Province Jerada','Province Taourirt','Province Figuig'
                ] as $prov)
                  <option value="{{ $prov }}" {{ old('province')===$prov ? 'selected' : '' }}>{{ $prov }}</option>
                @endforeach
              </select>
            </div>

            {{-- Commune principale --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Commune Principale <span class="text-danger">*</span></label>
              <select name="commune_1" id="commune1Select" class="form-select"
                      data-validate='{"required":true,"message":"Sélectionnez une commune"}'>
                <option value="">-- Sélectionner la commune --</option>
              </select>
            </div>

            {{-- Commune secondaire --}}
            <div class="mb-3">
              <label class="form-label">Commune à cheval (optionnel)</label>
              <select name="commune_2" id="commune2Select" class="form-select">
                <option value="">-- Sélectionner la commune --</option>
              </select>
            </div>

            {{-- Envoi papier --}}
            <div class="mb-3 form-check">
              <input type="checkbox" name="envoi_papier" class="form-check-input" id="envoi_papier" {{ old('envoi_papier') ? 'checked' : '' }}>
              <label for="envoi_papier" class="form-check-label fw-semibold">Envoi Papier ?</label>
            </div>

            <div id="envoi_details" class="mb-3 {{ old('envoi_papier') ? '' : 'd-none' }}">
              <div class="mb-2"><label class="form-label">Référence d’Envoi</label>
                <input type="text" name="reference_envoi" value="{{ old('reference_envoi') }}" class="form-control"></div>
              <div class="mb-2"><label class="form-label">Numéro d’Envoi</label>
                <input type="text" name="numero_envoi" value="{{ old('numero_envoi') }}" class="form-control"></div>
              <div class="mb-2"><label class="form-label">Numéro d’Arrivée</label>
                <input type="text" name="numero_arrivee" value="{{ old('numero_arrivee') }}" class="form-control"></div>
              </div>

            {{-- Dates --}}
            <div class="mb-3"><label class="form-label fw-semibold">Date d’Arrivée *</label>
              <input type="date" name="date_arrivee" value="{{ old('date_arrivee') }}" class="form-control"></div>

            <div class="mb-3"><label class="form-label fw-semibold">Date Commission Mixte (Rokhas)</label>
              <input type="date" name="date_commission_mixte" value="{{ old('date_commission_mixte') }}" class="form-control"></div>
          </div>

          {{-- ================= Colonne droite ================= --}}
          <div class="col-md-6">
            <h5 class="mb-3 text-secondary border-bottom pb-2">Informations du Projet</h5>

            <div class="mb-3"><label class="form-label fw-semibold">Pétitionnaire *</label>
              <input type="text" name="petitionnaire" value="{{ old('petitionnaire') }}" class="form-control"></div>

            <div class="mb-3 form-check">
              <input type="checkbox" id="has_proprietaire" class="form-check-input" {{ old('proprietaire') ? 'checked' : '' }}>
              <label for="has_proprietaire" class="form-check-label fw-semibold">Indiquer un Propriétaire distinct</label>
            </div>
            <div id="proprietaire_div" class="mb-3 {{ old('proprietaire') ? '' : 'd-none' }}">
              <label class="form-label">Propriétaire</label>
              <input type="text" name="proprietaire" value="{{ old('proprietaire') }}" class="form-control">
            </div>

            {{-- Catégorie pétitionnaire --}}
            @php $cats=['Amicale','association','Bienfaiteurs','Collectivité locale','coopérative','Etablissement public','OPH','Particulier','RME','Société Privé','Autre']; @endphp
            <div class="mb-3"><label class="form-label fw-semibold">Catégorie du Pétitionnaire *</label>
              <select name="categorie_petitionnaire" class="form-select">
                <option value="">-- Sélectionner --</option>
                @foreach($cats as $c)<option value="{{ $c }}" {{ old('categorie_petitionnaire','Particulier')===$c?'selected':'' }}>{{ $c }}</option>@endforeach
              </select>
            </div>

            <div class="mb-3"><label class="form-label fw-semibold">Intitulé du Projet *</label>
              <input type="text" name="intitule_projet" value="{{ old('intitule_projet') }}" class="form-control"></div>

            <div class="mb-3"><label class="form-label">Lien GED</label>
              <input type="url" name="lien_ged" value="{{ old('lien_ged') }}" class="form-control"></div>

            {{-- Catégorie projet --}}
            @php $catsProjet=['Commerce','Culte','Equipement de proximité','équipement public','équipement privé','Immeuble','projet agricole','Projet Industriel','Projet touristique','R+1','R+2','RDC','Services','Villa','Autre']; @endphp
            <div class="mb-3"><label class="form-label fw-semibold">Catégorie Projet *</label>
              <select name="categorie_projet" class="form-select">
                <option value="">-- Sélectionner --</option>
                @foreach($catsProjet as $c)<option value="{{ $c }}" {{ old('categorie_projet')===$c?'selected':'' }}>{{ $c }}</option>@endforeach
              </select>
            </div>

            {{-- Contexte --}}
            @php $ctx=['Assistance Architecturale Douar délimité','Douar non délimité','GP - Régularisation','INDH','Intégration de lots','PP- Régularisation','Régularisation','Relogement','VSB','ZAP']; @endphp
            <div class="mb-3"><label class="form-label fw-semibold">Contexte</label>
              <select name="contexte_projet" class="form-select"><option value="">-- Optionnel --</option>
                @foreach($ctx as $c)<option value="{{ $c }}" {{ old('contexte_projet')===$c?'selected':'' }}>{{ $c }}</option>@endforeach
              </select>
            </div>

            {{-- Maître d’œuvre --}}
            <div class="mb-3"><label class="form-label fw-semibold">Maître d’Œuvre *</label>
              <select name="maitre_oeuvre" class="form-select"><option value="">-- Sélectionner --</option>
                @foreach($maitresOeuvre as $mo)<option value="{{ $mo->nom }}" {{ old('maitre_oeuvre')===$mo->nom?'selected':'' }}>{{ $mo->nom }}</option>@endforeach
              </select>
            </div>

            <div class="mb-3"><label class="form-label fw-semibold">Situation *</label>
              <textarea name="situation" class="form-control">{{ old('situation') }}</textarea></div>

            <div class="mb-3"><label class="form-label fw-semibold">Références Foncières</label>
              <input type="text" name="reference_fonciere" value="{{ old('reference_fonciere') }}" class="form-control"></div>

            <div class="mb-3"><label class="form-label">Observations</label>
              <textarea name="observations" class="form-control">{{ old('observations') }}</textarea></div>
          </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
          <button class="btn btn-success"><i class="fas fa-save me-1"></i> Enregistrer</button>
          @if(Auth::user()->hasRole('chef'))
            <a href="{{ route('chef.grandprojets.cpc.index') }}" class="btn btn-secondary">Annuler</a>
          @else
            <a href="{{ route('saisie_cpc.dashboard') }}" class="btn btn-secondary">Annuler</a>
          @endif
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ================= Scripts ================= --}}
<script>
const provincesCommunes = {
  'Préfecture Oujda-Angad':[
    "Commune d'Ahl Angad","Commune d'Ain Sfa","Commune d'Isly","Commune d'Oujda","Commune d'Oujda Sidi Ziane",
    "Commune de Beni Drar","Commune de Beni Khaled","Commune de Labsara","Commune de Mestferki","Commune de Neima",
    "Commune de Oued Ennachef Sidi Mâafa","Commune de Sidi Boulenouar","Commune de Sidi Driss El Qadi",
    "Commune de Sidi Moussa Lemhaya","Commune de Sidi Yahya"
  ],
  'Province Berkane':[
    "Commune d'Aghbal","Commune d'Ahfir","Commune d'Aklim","Commune de Ain Reggada","Commune de Berkane",
    "Commune de Boughriba","Commune de Chouihya","Commune de Fezouane","Commune de Laâtamna","Commune de Madagh",
    "Commune de Rislane","Commune de Saïdia","Commune de Sidi Bouhria","Commune de Sidi Slimane Cheraa",
    "Commune de Tafoughalt","Commune de Zegzel"
  ],
  'Province Jerada':[
    "Commune de Ain Beni Mathar","Commune de Beni Mathar","Commune de Gafait","Commune de Guenfouda",
    "Commune de Jerada","Commune de Laouinate","Commune de Lebkhata","Commune de Mrija","Commune de Oulad Ghziyel",
    "Commune de Oulad Sidi Abdelhakem","Commune de Ras Asfour","Commune de Sidi Boubker","Commune de Tiouli","Commune de Touissit"
  ],
  'Province Taourirt':[
    "Commune d'Ahl Oued Za","Commune de Ain Lehjer","Commune de Debdou","Commune de Gteter","Commune de Machraa Hammadi",
    "Commune de Melg El Ouidane","Commune de Mestegmer","Commune de Oulad M'Hammed","Commune de Sidi Ali Belkassem",
    "Commune de Sidi Lahcen","Commune de Tancherfi","Commune de Taourirt","Commune d'El Aioun Sidi Mellouk","Commune d'El Atef"
  ],
  'Province Figuig':[
    "Commune de Abbou-Lakhal","Commune de Ain Chair","Commune de Ain Chouater","Commune de Beni Guil",
    "Commune de Beni Tadjite","Commune de Bouanane","Commune de Bouarfa","Commune de Bouchaouene",
    "Commune de Boumerieme","Commune de Figuig","Commune de Maatarka","Commune de Talsint","Commune de Tendrara"
  ]
};

function fillCommunes() {
  const prov = document.getElementById('provinceSelect').value;
  const c1   = document.getElementById('commune1Select');
  const c2   = document.getElementById('commune2Select');

  c1.innerHTML = '<option value="">-- Sélectionner la commune --</option>';
  c2.innerHTML = '<option value="">-- Sélectionner la commune --</option>';

  if (provincesCommunes[prov]) {
    provincesCommunes[prov].forEach(cm => {
      c1.add(new Option(cm, cm));
      c2.add(new Option(cm, cm));
    });
    // Rétablir anciens choix (post-validation)
    const oldC1 = @json(old('commune_1'));
    const oldC2 = @json(old('commune_2'));
    if (oldC1) c1.value = oldC1;
    if (oldC2) c2.value = oldC2;
  }
}
document.addEventListener('DOMContentLoaded', fillCommunes);
document.getElementById('provinceSelect').addEventListener('change', fillCommunes);
</script>

<script>
(function(){
  const numeroInput = document.querySelector('input[name="numero_dossier"]');
  const banner      = document.getElementById('dupBanner');
  const examNoSpan  = document.getElementById('examNo');
  const openLink    = document.getElementById('openExistingLink');
  const avisWrap    = document.getElementById('dupAvisWrap');
  const avisSpan    = document.getElementById('dupAvis');

  // ---- Helper PRE-FILL avec gestion Province -> Communes ----
  function setIfEmpty(name, value){
    if (!value) return;
    const el = document.querySelector(`[name="${CSS.escape(name)}"]`);
    if (!el) return;

    if (name === 'province') {
      // 1) fixer la province
      el.value = value;
      // 2) recharger la liste des communes (dépendante)
      fillCommunes();
    } else if (name === 'commune_1' || name === 'commune_2') {
      // Après fillCommunes, les options existent
      el.value = value;
    } else {
      if (!el.value) el.value = value;
    }
  }

  function hide(){
    banner.classList.add('d-none');
    avisWrap.classList.add('d-none');
  }

  async function lookupNumero(){
    const numero = (numeroInput.value || '').trim();
    if (!/^\d+\/\d{2}$/.test(numero)) { hide(); return; }

    try {
      const url  = `{{ route('ajax.lookup.dossier') }}?type=cpc&numero_dossier=${encodeURIComponent(numero)}`;
      const resp = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
      if (!resp.ok) throw new Error();
      const data = await resp.json();
      if (!data.ok || !data.exists) { hide(); return; }

      // Bandeau + lien
      const n = data.next_examen || 2;
      examNoSpan.textContent = (n === 1) ? '1er' : `${n}ᵉ`;
      openLink.href = "{{ route('cpc.show.shared', 0) }}".replace('/0', `/${data.gp_id}`);

      if (data.last_avis) {
        avisWrap.classList.remove('d-none');
        avisSpan.textContent = data.last_avis;
      } else {
        avisWrap.classList.add('d-none');
        avisSpan.textContent = '';
      }
      banner.classList.remove('d-none');

      // Pré-remplissage orchestré (ordre important)
      const pf = data.prefill || {};
      setIfEmpty('province', pf.province);          // déclenche fillCommunes()
      setIfEmpty('commune_1', pf.commune_1);
      setIfEmpty('commune_2', pf.commune_2);

      // Le reste des champs
      setIfEmpty('date_arrivee', pf.date_arrivee);
      setIfEmpty('numero_arrivee', pf.numero_arrivee);
      setIfEmpty('petitionnaire', pf.petitionnaire);
      setIfEmpty('proprietaire', pf.proprietaire);
      setIfEmpty('categorie_petitionnaire', pf.categorie_petitionnaire);
      setIfEmpty('intitule_projet', pf.intitule_projet);
      setIfEmpty('maitre_oeuvre', pf.maitre_oeuvre);
      setIfEmpty('situation', pf.situation);
      setIfEmpty('reference_fonciere', pf.reference_fonciere);
      setIfEmpty('reference_envoi', pf.reference_envoi);
      setIfEmpty('numero_envoi', pf.numero_envoi);
      setIfEmpty('date_commission_mixte', pf.date_commission_mixte);
      setIfEmpty('lien_ged', pf.lien_ged);
      setIfEmpty('categorie_projet', pf.categorie_projet);
      setIfEmpty('contexte_projet', pf.contexte_projet);
      setIfEmpty('observations', pf.observations);

    } catch(e) {
      hide();
    }
  }

  numeroInput.addEventListener('blur', lookupNumero);
})();
</script>

<script>
  // Petits helpers UI
  document.getElementById('has_proprietaire').addEventListener('change', function(){
    document.getElementById('proprietaire_div').classList.toggle('d-none', !this.checked);
  });
  document.getElementById('envoi_papier').addEventListener('change', function(){
    document.getElementById('envoi_details').classList.toggle('d-none', !this.checked);
  });
</script>
@endsection
