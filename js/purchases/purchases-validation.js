/**
 * =====================================================
 * 🛒 SECTION: ACHATS ET DÉPENSES - CALCULS & VALIDATION
 * =====================================================
 * 
 * Fonctions pour les calculs financiers et validation des formulaires
 */

/**
 * =====================================================
 * 💰 CALCULS FINANCIERS
 * =====================================================
 */

/**
 * Calcule les montants HT et TVA à partir du montant TTC
 */
function calculateFinancialsFromTTC() {
    console.log('🧮 Calcul des montants depuis le TTC...');
    
    const ttcInput = document.getElementById('achat-montant-ttc');
    const htInput = document.getElementById('achat-montant-ht');
    const tvaInput = document.getElementById('achat-taux-tva');
    const tvaOutput = document.getElementById('achat-tva-calcule');
    const ttcOutput = document.getElementById('achat-ttc-calcule');
    
    if (!ttcInput || !htInput || !tvaInput) {
        console.warn('⚠️ Champs de calcul non trouvés');
        return;
    }
    
    const ttc = parseFloat(ttcInput.value) || 0;
    const tauxTva = parseFloat(tvaInput.value) || 0;
    
    if (ttc > 0 && tauxTva > 0) {
        const ht = ttc / (1 + tauxTva / 100);
        const tva = ttc - ht;
        
        htInput.value = ht.toFixed(2);
        if (tvaOutput) tvaOutput.textContent = tva.toFixed(2) + ' €';
        if (ttcOutput) ttcOutput.textContent = ttc.toFixed(2) + ' €';
        
        console.log(`✅ Calculs: HT=${ht.toFixed(2)}€, TVA=${tva.toFixed(2)}€, TTC=${ttc.toFixed(2)}€`);
    }
    
    updateAchatRecap();
}

/**
 * Calcule les montants à partir du montant HT
 */
function calculateFinancials() {
    console.log('🧮 Calcul des montants depuis le HT...');
    
    const htInput = document.getElementById('achat-montant-ht');
    const tvaInput = document.getElementById('achat-taux-tva');
    const ttcInput = document.getElementById('achat-montant-ttc');
    const tvaOutput = document.getElementById('achat-tva-calcule');
    const ttcOutput = document.getElementById('achat-ttc-calcule');
    
    if (!htInput || !tvaInput) {
        console.warn('⚠️ Champs de calcul HT non trouvés');
        return;
    }
    
    const ht = parseFloat(htInput.value) || 0;
    const tauxTva = parseFloat(tvaInput.value) || 0;
    const remise = parseFloat(document.getElementById('achat-remise')?.value) || 0;
    const timbre = parseFloat(document.getElementById('achat-timbre')?.value) || 0;
    
    if (ht > 0 && tauxTva > 0) {
        const tva = ht * (tauxTva / 100);
        const ttc = ht + tva - remise + timbre;
        
        if (ttcInput) ttcInput.value = ttc.toFixed(2);
        if (tvaOutput) tvaOutput.textContent = tva.toFixed(2) + ' €';
        if (ttcOutput) ttcOutput.textContent = ttc.toFixed(2) + ' €';
        
        console.log(`✅ Calculs HT: HT=${ht.toFixed(2)}€, TVA=${tva.toFixed(2)}€, TTC=${ttc.toFixed(2)}€`);
    }
    
    updateAchatRecap();
}

/**
 * Met à jour le récapitulatif de l'achat
 */
function updateAchatRecap() {
    console.log('📋 Mise à jour du récapitulatif...');
    
    // Récupérer les valeurs du formulaire
    const fournisseur = document.getElementById('achat-fournisseur');
    const reference = document.getElementById('achat-reference');
    const categorie = document.getElementById('achat-categorie-pieces');
    const ttc = parseFloat(document.getElementById('achat-montant-ttc')?.value) || 0;
    
    // Mettre à jour les éléments du récapitulatif
    const recapFournisseur = document.getElementById('recap-fournisseur');
    const recapReference = document.getElementById('recap-reference');
    const recapCategorie = document.getElementById('recap-categorie');
    const recapTtc = document.getElementById('recap-ttc');
    
    if (recapFournisseur) {
        recapFournisseur.textContent = `Fournisseur: ${fournisseur?.selectedOptions[0]?.textContent || '-'}`;
    }
    
    if (recapReference) {
        recapReference.textContent = `Référence: ${reference?.value || '-'}`;
    }
    
    if (recapCategorie) {
        recapCategorie.textContent = `Catégorie: ${categorie?.selectedOptions[0]?.textContent || '-'}`;
    }
    
    if (recapTtc) {
        recapTtc.textContent = `TTC: ${ttc.toFixed(2)} €`;
    }
    
    // Synchroniser les montants avec l'onglet 3 (Paiement & État)
    if (typeof syncTotalAmount === 'function') {
        try {
            syncTotalAmount();
        } catch (error) {
            console.warn('⚠️ Impossible de synchroniser avec l\'onglet paiement:', error);
        }
    }
    
    // NOTE: Ne plus appeler calculateFinancialsFromTTC pour éviter la récursion infinie
}

/**
 * =====================================================
 * ✅ VALIDATION DES FORMULAIRES
 * =====================================================
 */

/**
 * Valide le formulaire d'achat (VERSION UNIFIÉE)
 * @param {Object} formData - Données du formulaire
 * @returns {Object} Résultat de la validation {valid: boolean, errors: string[]}
 */
function validateAchatForm(formData = null) {
    console.log('✅ Validation du formulaire d\'achat...');
    
    // Si pas de données fournies, récupérer depuis le formulaire
    if (!formData) {
        formData = collectAchatData();
    }
    
    const errors = [];
    
    // ✅ VALIDATION DES 4 CHAMPS OBLIGATOIRES UNIQUEMENT
    if (!formData.fournisseur_id || formData.fournisseur_id === '') {
        errors.push('Le fournisseur est obligatoire');
    }
    
    if (!formData.reference || formData.reference.trim() === '') {
        errors.push('La référence est obligatoire');
    }
    
    if (!formData.montant_ttc || formData.montant_ttc <= 0) {
        errors.push('Le montant TTC doit être supérieur à 0');
    }
    
    if (!formData.date_facture) {
        errors.push('La date de facture est obligatoire');
    }
    
    // ✅ CHAMPS FACULTATIFS (ne bloquent plus l'enregistrement) :
    // - Description (facultative)
    // - Compte bancaire (facultatif)
    // - Date d'échéance (facultative)
    // - Montant HT (calculé automatiquement)
    // - Taux TVA (défaut 20%)
    // - Remise (optionnelle)
    // - Timbre (optionnel)
    // - Mode de paiement (par défaut)
    // - Statut de paiement (par défaut)
    // - Date de paiement (optionnelle)
    // - Notes internes (facultatives)
    // - Mots-clés (facultatifs)
    
    // Validation des montants
    if (formData.montant_ttc && formData.montant_ttc > 1000000) {
        errors.push('Le montant semble excessif (maximum 1 000 000 €)');
    }
    
    // Validation des dates
    if (formData.date_facture && formData.date_echeance) {
        const dateFacture = new Date(formData.date_facture);
        const dateEcheance = new Date(formData.date_echeance);
        
        if (dateEcheance < dateFacture) {
            errors.push('La date d\'échéance doit être postérieure à la date de facture');
        }
    }
    
    const isValid = errors.length === 0;
    
    console.log(`✅ Validation: ${isValid ? 'VALIDE' : 'ERREURS'} (${errors.length} erreur${errors.length > 1 ? 's' : ''})`);
    
    if (!isValid) {
        showNotification(`Erreurs de validation: ${errors.join(', ')}`, 'error');
    }
    
    return {
        valid: isValid,
        errors: errors
    };
}

/**
 * Met à jour le statut de paiement et affiche/cache les champs appropriés
 */
function updatePaymentStatus() {
    console.log('💳 Mise à jour du statut de paiement...');
    
    const statutSelect = document.getElementById('achat-statut');
    const paymentDateRow = document.getElementById('payment-date-row');
    
    if (!statutSelect || !paymentDateRow) {
        return;
    }
    
    const statut = statutSelect.value;
    
    // Afficher/masquer la date de paiement selon le statut
    if (statut === 'paye') {
        paymentDateRow.style.display = 'flex';
        const dateField = document.getElementById('achat-date-paiement');
        if (dateField && !dateField.value) {
            dateField.value = new Date().toISOString().split('T')[0];
        }
    } else {
        paymentDateRow.style.display = 'none';
    }
    
    console.log(`📊 Statut de paiement: ${statut}`);
}

/**
 * =====================================================
 * 📊 COLLECTE DE DONNÉES
 * =====================================================
 */

/**
 * Collecte toutes les données du formulaire d'achat
 * @returns {Object} Données du formulaire
 */
function collectAchatData() {
    console.log('📋 Collecte des données du formulaire...');
    
    const formData = {
        // Informations principales
        fournisseur_id: document.getElementById('achat-fournisseur')?.value || '',
        reference: document.getElementById('achat-reference')?.value || '',
        description: document.getElementById('achat-description')?.value || '',
        montant_ttc: parseFloat(document.getElementById('achat-montant-ttc')?.value) || 0,
        categorie_id: document.getElementById('achat-categorie-pieces')?.value || '',
        date_facture: document.getElementById('achat-date-facture')?.value || '',
        date_echeance: document.getElementById('achat-date-echeance')?.value || '',
        
        // Informations financières
        montant_ht: parseFloat(document.getElementById('achat-montant-ht')?.value) || 0,
        taux_tva: parseFloat(document.getElementById('achat-taux-tva')?.value) || 0,
        remise: parseFloat(document.getElementById('achat-remise')?.value) || 0,
        timbre: parseFloat(document.getElementById('achat-timbre')?.value) || 0,
        
        // Paiement
        mode_paiement: document.getElementById('achat-mode-paiement')?.value || '',
        compte_id: document.getElementById('achat-compte')?.value || '',
        statut_paiement: document.getElementById('achat-statut')?.value || 'a_payer',
        date_paiement: document.getElementById('achat-date-paiement')?.value || '',
        
        // Documents et notes
        notes_internes: document.getElementById('achat-notes-internes')?.value || '',
        mots_cles: document.getElementById('achat-mots-cles')?.value || '',
        auto_generate_ref: document.getElementById('achat-auto-generate-ref')?.checked || false
    };
    
    console.log(`📊 Données collectées: ${Object.keys(formData).length} champs`);
    return formData;
}