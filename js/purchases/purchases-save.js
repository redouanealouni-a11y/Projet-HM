/**
 * =====================================================
 * 🛒 SECTION: ACHATS ET DÉPENSES - SAUVEGARDE & DOCUMENTS
 * =====================================================
 * 
 * Fonctions pour la sauvegarde des achats et gestion des documents
 */

/**
 * =====================================================
 * 💾 SAUVEGARDE DES ACHATS
 * =====================================================
 */

/**
 * Sauvegarde un achat (VERSION UNIFIÉE)
 * @param {boolean} continueAfter - Continuer après sauvegarde (garder modal ouvert)
 * @returns {Promise<Object>} Résultat de la sauvegarde
 */
async function saveAchat(continueAfter = false) {
    console.log(`💾 Sauvegarde achat (continuer: ${continueAfter})...`);
    
    try {
        // Collecter et valider les données
        const formData = collectAchatData();
        const validation = validateAchatForm(formData);
        
        if (!validation.valid) {
            console.warn('❌ Validation échouée:', validation.errors);
            return { success: false, errors: validation.errors };
        }
        
        // Afficher un indicateur de chargement
        showSavingIndicator(true);
        
        // Déterminer l'URL et la méthode selon le mode (ajout/modification)
        const isEdit = editingId !== null;
        const url = isEdit ? `api/pieces_tresorerie.php?action=update_achat&id=${editingId}` : 'api/pieces_tresorerie.php?action=create_achat';
        const method = 'POST';
        
        console.log(`${isEdit ? '✏️ Modification' : '➕ Ajout'} achat via ${method} ${url}`);
        
        // ✅ CORRECTION: Utiliser les noms exacts des colonnes de la base de données
        const pieceData = {
            // ✅ MAPPING EXACT SELON LE SCHÉMA DB
            cletypedocument: 'facture_achat',
            cletiers: formData.fournisseur_id || null,
            clecompte: formData.compte_id || null, // Nullable - pas obligatoire pour l'achat
            label: formData.description || formData.reference || 'Achat',
            date: formData.date_facture || new Date().toISOString().split('T')[0],
            dateecheance: formData.date_echeance || null, // Date d'échéance
            montantttc: formData.montant_ttc || 0,
            
            // ✅ CHAMPS OPTIONNELS
            reference: formData.reference || null,
            note: formData.notes_internes || null,
            noteinterne: formData.mots_cles || null,
            payement: formData.mode_paiement || 'especes',
            cleetatdocument: 'brouillon', // Statut de la pièce
            
            // ✅ CALCULS AUTOMATIQUES
            montantht: formData.montant_ht || Math.round((formData.montant_ttc || 0) / 1.19 * 100) / 100,
            tauxtva: formData.taux_tva || 19,
            totaltva: formData.taux_tva ? Math.round((formData.montant_ttc || 0) * formData.taux_tva / 100 * 100) / 100 : Math.round((formData.montant_ttc || 0) * 0.19 * 100) / 100,
            remise: formData.remise || 0,
            timbre: formData.timbre || 0
        };
        
        console.log('📊 Données pour pièce de trésorerie:', pieceData);
        
        const response = await apiCall(url, {
            method: method,
            body: JSON.stringify(pieceData)
        });
        
        if (response.success) {
            console.log('✅ Achat sauvegardé avec succès:', response.data);
            
            // ✅ ADAPTATION POUR PIÈCES DE TRÉSORERIE
            // Les achats sont maintenant des pièces de trésorerie, pas des transactions
            const achatData = response.data;
            
            // Afficher une notification de succès
            showNotification(
                `Achat ${isEdit ? 'modifié' : 'ajouté'} avec succès`, 
                'success'
            );
            
            // Fermer ou continuer selon le paramètre
            if (continueAfter) {
                resetAchatForm();
                loadAchatDropdowns(); // Recharger les listes
            } else {
                closeAchatModal();
                editingId = null; // Réinitialiser le mode édition
            }
            
            // Recharger les données pour afficher les changements
            loadAchatsEnregistrements();
            
            return { success: true, data: achatData };
        } else {
            console.error('❌ Erreur lors de la sauvegarde:', response);
            showNotification(response.message || 'Erreur lors de la sauvegarde', 'error');
            return { success: false, message: response.message };
        }
        
    } catch (error) {
        console.error('❌ Erreur lors de la sauvegarde de l\'achat:', error);
        showNotification('Erreur lors de la sauvegarde', 'error');
        return { success: false, error: error.message };
    } finally {
        showSavingIndicator(false);
    }
}

/**
 * Gestionnaire d'événement pour la sauvegarde via formulaire
 * @param {Event} event 
 */
async function saveAchatFromForm(event) {
    event.preventDefault();
    console.log('📝 Soumission du formulaire d\'achat...');
    
    // ✅ VALIDATION SIMPLIFIÉE - SEULS 4 CHAMPS OBLIGATOIRES
    // Les autres champs sont maintenant FACULTATIFS et ne bloquent plus l'enregistrement
    
    const statutPayment = document.getElementById('achat-statut')?.value;
    const datePaiementField = document.getElementById('achat-date-paiement');
    
    if (statutPayment && statutPayment === 'paye') {
        // ✅ UNIQUEMENT la date de paiement est requise quand statut = 'paye'
        if (datePaiementField?.hasAttribute('required') && !datePaiementField.value) {
            console.warn('⚠️ Date de paiement requise pour statut "payé"');
            showNotification('Date de paiement requise quand le statut est "Payé"', 'warning');
            return;
        }
    }
    
    // ✅ COMPTE BANCAIRE N'EST PLUS OBLIGATOIRE
    // ✅ MONTANT PAYÉ N'EST PLUS OBLIGATOIRE  
    // ✅ TOUS LES AUTRES CHAMPS SONT FACULTATIFS
    
    const continueAfter = event.submitter?.dataset.continue === 'true';
    const result = await saveAchat(continueAfter);
    
    if (result.success) {
        // Actions post-sauvegarde réussies
        console.log('✅ Achat enregistré depuis le formulaire');
        
        // Fermer le modal si ce n'est pas une sauvegarde continue
        if (!continueAfter) {
            console.log('🚪 Fermeture du modal après sauvegarde...');
            closeAchatModal();
            
            // Recharger les données d'achats pour afficher le nouvel achat
            loadAchatsEnregistrements();
        }
    } else {
        // Afficher les erreurs de validation
        console.error('❌ Erreurs de validation:', result.errors);
        if (result.errors && result.errors.length > 0) {
            showNotification('Erreurs de validation: ' + result.errors.join(', '), 'error');
        }
    }
}

/**
 * =====================================================
 * 📎 GESTION DES DOCUMENTS
 * =====================================================
 */

/**
 * Gère la sélection de fichiers pour l'achat
 * @param {Event} event 
 */
function handleAchatFileSelection(event) {
    console.log('📎 Sélection de fichiers pour achat...');
    
    const files = Array.from(event.target.files);
    const uploadZone = document.getElementById('upload-zone');
    const uploadedFiles = document.getElementById('uploaded-files');
    
    if (!uploadZone || !uploadedFiles) {
        console.warn('⚠️ Éléments d\'upload non trouvés');
        return;
    }
    
    // Ajouter les fichiers à la liste
    files.forEach(file => {
        if (file.size > 10 * 1024 * 1024) { // 10MB max
            showNotification(`Le fichier "${file.name}" est trop volumineux (max 10MB)`, 'warning');
            return;
        }
        
        // Ajouter le fichier à la zone d'affichage
        const fileElement = createFileElement(file);
        uploadedFiles.appendChild(fileElement);
    });
    
    // Mettre à jour l'apparence de la zone d'upload
    uploadZone.classList.toggle('has-files', uploadedFiles.children.length > 0);
    
    console.log(`📎 ${files.length} fichier(s) ajouté(s)`);
}

/**
 * Crée un élément visuel pour afficher un fichier sélectionné
 * @param {File} file 
 * @returns {HTMLElement} Élément du fichier
 */
function createFileElement(file) {
    const fileDiv = document.createElement('div');
    fileDiv.className = 'uploaded-file';
    fileDiv.innerHTML = `
        <div class="file-info">
            <i class="fas fa-file-${getFileIcon(file.type)}"></i>
            <span class="file-name">${sanitizeHTML(file.name)}</span>
            <span class="file-size">${formatFileSize(file.size)}</span>
        </div>
        <button type="button" class="remove-file" onclick="removeFile(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Stocker le fichier dans l'élément
    fileDiv.file = file;
    
    return fileDiv;
}

/**
 * Supprime un fichier de la liste des fichiers uploadés
 * @param {HTMLElement} button 
 */
function removeFile(button) {
    const fileDiv = button.closest('.uploaded-file');
    if (fileDiv) {
        fileDiv.remove();
        
        // Mettre à jour l'apparence de la zone d'upload
        const uploadZone = document.getElementById('upload-zone');
        const uploadedFiles = document.getElementById('uploaded-files');
        if (uploadZone && uploadedFiles) {
            uploadZone.classList.toggle('has-files', uploadedFiles.children.length > 0);
        }
        
        console.log('📎 Fichier supprimé de la liste');
    }
}

/**
 * Upload un document pour un achat existant
 * @param {number} transactionId - ID de la transaction
 * @param {File} file - Fichier à uploader
 * @returns {Promise<Object>} Résultat de l'upload
 */
async function uploadAchatDocument(transactionId, file) {
    console.log(`📤 Upload document pour achat ${transactionId}...`);
    
    try {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('transaction_id', transactionId);
        formData.append('type', 'achat');
        
        const response = await fetch(`${API_BASE}/upload.php`, {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            console.log('✅ Document uploadé avec succès:', result.data);
            showNotification('Document uploadé avec succès', 'success');
            return { success: true, data: result.data };
        } else {
            console.error('❌ Erreur upload:', result.message);
            showNotification(result.message || 'Erreur lors de l\'upload', 'error');
            return { success: false, message: result.message };
        }
        
    } catch (error) {
        console.error('❌ Erreur lors de l\'upload du document:', error);
        showNotification('Erreur lors de l\'upload', 'error');
        return { success: false, error: error.message };
    }
}

/**
 * =====================================================
 * 🛠️ FONCTIONS UTILITAIRES
 * =====================================================
 */

/**
 * Duplique l'achat actuel
 */
function duplicateCurrentAchat() {
    console.log('📋 Duplication de l\'achat actuel...');
    
    // Collecter les données actuelles
    const currentData = collectAchatData();
    
    // Vider les champs qui ne doivent pas être dupliqués
    delete currentData.id;
    delete currentData.reference; // Peut être régénéré
    
    // Réinitialiser le formulaire avec les nouvelles données
    prefillAchatForm(currentData);
    
    // Ajuster le titre de la modal
    const modal = document.getElementById('achatModal');
    const title = modal?.querySelector('.modal-header h2');
    if (title) {
        title.innerHTML = '<i class="fas fa-copy"></i> Dupliquer Achat';
    }
    
    showNotification('Formulaire préparé pour duplication', 'info');
    console.log('✅ Achat dupliqué dans le formulaire');
}

/**
 * Exporte les données d'achat
 */
function exportAchatData() {
    console.log('📊 Export des données d\'achat...');
    
    // Collecter toutes les données d'achats
    const achatsData = appData.transactions.filter(t => t.type === 'achat');
    
    if (achatsData.length === 0) {
        showNotification('Aucune donnée d\'achat à exporter', 'warning');
        return;
    }
    
    // Convertir en CSV
    const csv = convertToCSV(achatsData);
    
    // Télécharger le fichier
    downloadCSV(csv, `achats_${new Date().toISOString().split('T')[0]}.csv`);
    
    showNotification('Données d\'achat exportées', 'success');
    console.log(`📊 ${achatsData.length} achats exportés`);
}

/**
 * Affiche/masque l'indicateur de sauvegarde
 * @param {boolean} show 
 */
function showSavingIndicator(show) {
    // Chercher le bouton de sauvegarde actif
    const saveButton = document.querySelector('#achatModal button[type="submit"]');
    if (saveButton) {
        saveButton.disabled = show;
        saveButton.innerHTML = show 
            ? '<i class="fas fa-spinner fa-spin"></i> Sauvegarde...'
            : '<i class="fas fa-save"></i> Enregistrer';
    }
}