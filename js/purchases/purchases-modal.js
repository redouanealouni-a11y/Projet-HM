/**
 * =====================================================
 * 🛒 SECTION: ACHATS ET DÉPENSES - MODALS PRINCIPALES
 * =====================================================
 * 
 * Fonctions de gestion des modals pour les achats
 * UNIFICATION: Élimination des fonctions dupliquées
 */

/**
 * =====================================================
 * 🎯 MODAL ACHAT PRINCIPAL (UNIFIÉE)
 * =====================================================
 */

/**
 * Ouvre la modal d'ajout/modification d'achat (VERSION UNIFIÉE)
 */
function openAchatModal(achatId = null) {
    console.log(`🛒 Ouverture modal achat ${achatId ? '(modification)' : '(nouvel achat)'}...`);
    
    const modal = document.getElementById('achatModal');
    if (!modal) {
        console.error('❌ Modal achat non trouvé');
        return;
    }
    
    // Réinitialiser le formulaire
    resetAchatForm();
    
    // Configurer le titre selon le mode
    const title = modal.querySelector('.modal-header h2');
    if (title) {
        title.innerHTML = achatId 
            ? '<i class="fas fa-edit"></i> Modifier Achat'
            : '<i class="fas fa-shopping-cart"></i> Nouvel Achat';
    }
    
    // Charger les données si c'est une modification
    if (achatId) {
        loadAchatForEdit(achatId);
    }
    
    // Charger les listes déroulantes
    loadAchatDropdowns();
    
    // Afficher la modal
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    // Focus sur le premier champ
    setTimeout(() => {
        const firstInput = modal.querySelector('#achat-fournisseur');
        if (firstInput) firstInput.focus();
    }, 100);
    
    // Initialiser l'onglet paiement & état
    initializePaymentTab();
    
    console.log('✅ Modal achat ouverte');
}

// Exposer les fonctions pour les onglets
window.updatePaymentStatus = updatePaymentStatus;
window.updatePaymentFields = updatePaymentFields;
window.calculatePaymentBalance = calculatePaymentBalance;
window.initializePaymentTab = initializePaymentTab;
window.syncTotalAmount = syncTotalAmount;

/**
 * Ferme la modal d'achat (VERSION UNIFIÉE)
 */
function closeAchatModal() {
    const modal = document.getElementById('achatModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        console.log('❌ Modal achat fermée');
    }
}

/**
 * Réinitialise le formulaire d'achat (VERSION UNIFIÉE)
 */
function resetAchatForm() {
    const form = document.getElementById('formNouvelAchat');
    if (form) {
        form.reset();
        console.log('🔄 Formulaire achat réinitialisé');
    }
    
    // Réinitialiser les champs spécifiques
    const today = new Date().toISOString().split('T')[0];
    const dateField = document.getElementById('achat-date-facture');
    if (dateField) {
        dateField.value = today;
    }
    
    // Réinitialiser le récapitulatif
    updateAchatRecap();
    
    // Réinitialiser le fichier d'upload
    const uploadZone = document.getElementById('upload-zone');
    const uploadedFiles = document.getElementById('uploaded-files');
    if (uploadZone) uploadZone.classList.remove('has-files');
    if (uploadedFiles) uploadedFiles.innerHTML = '';
}

/**
 * Navigue entre les onglets du modal achat
 * @param {string} tabName - Nom de l'onglet à afficher
 */
function switchAchatTab(tabName) {
    console.log(`🔄 Basculement vers onglet modal: ${tabName}`);
    
    // Désactiver tous les onglets
    const allTabButtons = document.querySelectorAll('.modal .tab-btn');
    const allTabContents = document.querySelectorAll('.modal .tab-content');
    
    allTabButtons.forEach(btn => btn.classList.remove('active'));
    allTabContents.forEach(content => content.classList.remove('active'));
    
    // Activer l'onglet sélectionné
    const activeButton = document.querySelector(`.tab-btn[onclick="switchAchatTab('${tabName}')"]`);
    const activeContent = document.getElementById(`tab-${tabName}`);
    
    if (activeButton) activeButton.classList.add('active');
    if (activeContent) activeContent.classList.add('active');
    
    // Actions spécifiques par onglet
    switch (tabName) {
        case 'main-info':
            updateAchatRecap();
            break;
        case 'financial':
            calculateFinancialsFromTTC();
            break;
        case 'payment':
            // Synchroniser les montants depuis l'onglet financial
            if (typeof syncTotalAmount === 'function') {
                syncTotalAmount();
            }
            // Initialiser l'onglet paiement
            updatePaymentStatus();
            break;
    }
}

/**
 * =====================================================
 * 🎯 MODAL CATÉGORIES (EXISTANT - FONCTIONNEL)
 * =====================================================
 */

/**
 * Ouvre la modal de gestion des catégories
 * @param {number|null} categorieId - ID de la catégorie à modifier (null pour nouvelle)
 */
// =====================================================
// 🏷️ MODAL CATÉGORIES - GESTION
// =====================================================
// NOTE: openCategoryModal est dans main.js (version principale)
//       Ce fichier ne contient que les fonctions de support

/**
 * NOTE: closeCategoryModal() est définie dans main.js pour éviter les doublons
 * et conflits. La version main.js utilise modal.classList.remove('show') qui
 * est compatible avec le système de classes du modal. Ne pas redéfinir ici.
 */

/**
 * ✅ SUPPRIMÉ: La fonction loadCategoryData a été déplacée vers main.js
 * pour éviter les conflits entre fichiers.
 * 
 * Cette version sera fournie par main.js via window.loadCategoryData
 */

/**
 * =====================================================
 * 🔧 FONCTIONS UTILITAIRES MODAL
 * =====================================================
 */

/**
 * Charge les données d'un achat pour modification
 * @param {number} achatId 
 */
async function loadAchatForEdit(achatId) {
    try {
        const response = await apiCall(`/transactions.php?id=${achatId}&type=achat`);
        if (response.success && response.data) {
            const achat = response.data;
            
            // Remplir le formulaire avec les données existantes
            prefillAchatForm(achat);
            console.log('✅ Données achat chargées pour modification');
        }
    } catch (error) {
        console.error('❌ Erreur lors du chargement des données achat:', error);
        showNotification('Erreur lors du chargement des données', 'error');
    }
}

/**
 * Remplit le formulaire avec les données d'un achat
 * @param {Object} achatData 
 */
function prefillAchatForm(achatData) {
    // Informations principales
    if (achatData.fournisseur_id) {
        document.getElementById('achat-fournisseur').value = achatData.fournisseur_id;
    }
    if (achatData.reference) {
        document.getElementById('achat-reference').value = achatData.reference;
    }
    if (achatData.description) {
        document.getElementById('achat-description').value = achatData.description;
    }
    if (achatData.montant_ttc) {
        document.getElementById('achat-montant-ttc').value = achatData.montant_ttc;
    }
    if (achatData.date_facture) {
        document.getElementById('achat-date-facture').value = achatData.date_facture;
    }
    if (achatData.date_echeance) {
        document.getElementById('achat-date-echeance').value = achatData.date_echeance;
    }
    
    // Informations financières
    if (achatData.montant_ht) {
        document.getElementById('achat-montant-ht').value = achatData.montant_ht;
    }
    if (achatData.taux_tva) {
        document.getElementById('achat-taux-tva').value = achatData.taux_tva;
    }
    if (achatData.remise) {
        document.getElementById('achat-remise').value = achatData.remise;
    }
    if (achatData.timbre) {
        document.getElementById('achat-timbre').value = achatData.timbre;
    }
    
    // Paiement
    if (achatData.mode_paiement) {
        document.getElementById('achat-mode-paiement').value = achatData.mode_paiement;
    }
    if (achatData.compte_id) {
        document.getElementById('achat-compte').value = achatData.compte_id;
    }
    if (achatData.statut_paiement) {
        document.getElementById('achat-statut').value = achatData.statut_paiement;
    }
    
    updateAchatRecap();
}

// ✅ SUPPRIMÉ: L'export global de loadCategoryData a été supprimé
// car cette fonction est maintenant définie uniquement dans main.js

/**
 * =====================================================
 * 🎯 GESTION ONGLET 3: PAIEMENT & ÉTAT
 * =====================================================
 */

/**
 * Mettre à jour les champs de paiement selon le statut
 */
function updatePaymentStatus() {
    try {
        const statut = document.getElementById('achat-statut').value;
        const montantTotal = getMontantTotal();
        
        console.log('💳 Mise à jour statut paiement:', statut, 'Montant total:', montantTotal);
        
        const paymentSection = document.getElementById('payment-details-section');
        const balanceSection = document.getElementById('payment-balance-section');
        const historySection = document.getElementById('payment-history-section');
        
        if (statut === 'non_paye') {
            // Masquer les détails de paiement
            paymentSection.style.display = 'none';
            balanceSection.style.display = 'none';
            historySection.style.display = 'none';
            
            // Réinitialiser les champs
            document.getElementById('achat-montant-payé').value = '';
            document.getElementById('achat-date-paiement').value = '';
            document.getElementById('achat-ref-paiement').value = '';
            document.getElementById('achat-note-paiement').value = '';
            
        } else if (statut === 'partiel' || statut === 'paye') {
            // Afficher les détails de paiement
            console.log('🔓 Affichage des détails de paiement pour statut:', statut);
            paymentSection.style.display = 'block';
            balanceSection.style.display = 'block';
            
            // ✅ CORRECTION : S'assurer que les comptes sont chargés
            const compteSelect = document.getElementById('achat-compte');
            if (compteSelect && compteSelect.options.length <= 1) {
                console.log('🔄 Rechargement des comptes car dropdown vide...');
                if (typeof loadComptesForAchat === 'function') {
                    loadComptesForAchat();
                }
            }
            
            // Pré-remplir avec le montant total si "payé"
            if (statut === 'paye') {
                document.getElementById('achat-montant-payé').value = montantTotal;
            }
            
            // Afficher le solde
            calculatePaymentBalance();
            
            // Historique pour paiements partiels
            if (statut === 'partiel') {
                historySection.style.display = 'block';
            } else {
                historySection.style.display = 'none';
            }
        }
        
        // Mettre à jour l'aide contextuelle
        updatePaymentHelpText();
        
        // Gérer les champs requis selon le statut
        const datePaiementField = document.getElementById('achat-date-paiement');
        const compteField = document.getElementById('achat-compte');
        const montantPayeField = document.getElementById('achat-montant-payé');
        
        if (statut === 'non_paye') {
            // Supprimer les required pour les champs masqués
            datePaiementField.removeAttribute('required');
            compteField.removeAttribute('required');
            montantPayeField.removeAttribute('required');
            console.log('🔓 Champs requis supprimés (statut: non_paye)');
        } else {
            // Ajouter les required pour les champs visibles
            datePaiementField.setAttribute('required', 'true');
            compteField.setAttribute('required', 'true');
            montantPayeField.setAttribute('required', 'true');
            console.log('🔒 Champs requis ajoutés (statut:', statut, ')');
        }
        
    } catch (error) {
        console.error('❌ Erreur mise à jour statut paiement:', error);
    }
}

/**
 * Mettre à jour les champs selon le mode de paiement
 */
function updatePaymentFields() {
    try {
        const mode = document.getElementById('achat-mode-paiement').value;
        
        console.log('💳 Mode de paiement changé:', mode);
        
        // Ajouter des messages d'aide contextuelle selon le mode
        const helpElement = document.getElementById('payment-help');
        let helpText = '';
        
        switch (mode) {
            case 'especes':
                helpText = 'Spécifiez le montant exact donné et rendu si applicable';
                break;
            case 'cheque':
                helpText = 'Nº chèque: sur l\'enveloppe ou le reçu de dépôt';
                break;
            case 'virement':
                helpText = 'Référence virement: IBAN ou mention sur le relevé';
                break;
            case 'carte_bancaire':
                helpText = 'Ticket de carte ou numéro d\'autorisation';
                break;
            case 'prelevement':
                helpText = 'Référez-vous au mandat de prélèvement';
                break;
            default:
                helpText = 'Détaillez la référence pour faciliter le suivi';
        }
        
        if (helpElement) {
            helpElement.textContent = helpText;
        }
        
    } catch (error) {
        console.error('❌ Erreur mise à jour champs paiement:', error);
    }
}

/**
 * Calculer le solde de paiement
 */
function calculatePaymentBalance() {
    try {
        const montantTotal = getMontantTotal();
        const montantPaye = parseFloat(document.getElementById('achat-montant-payé').value) || 0;
        const reste = montantTotal - montantPaye;
        
        console.log('💰 Calcul solde:', { montantTotal, montantPaye, reste });
        
        // Mettre à jour l'affichage
        document.getElementById('montant-total-affiche').textContent = formatCurrency(montantTotal);
        document.getElementById('montant-payé-affiche').textContent = formatCurrency(montantPaye);
        document.getElementById('reste-a-payer').textContent = formatCurrency(reste);
        
        // Couleur selon le statut
        const resteElement = document.getElementById('reste-a-payer');
        const statut = document.getElementById('achat-statut').value;
        
        if (statut === 'paye') {
            resteElement.style.color = '#22c55e'; // Vert
        } else if (statut === 'partiel' && reste > 0) {
            resteElement.style.color = '#eab308'; // Orange
        } else {
            resteElement.style.color = '#ef4444'; // Rouge
        }
        
        // Validation des montants
        validatePaymentAmounts();
        
    } catch (error) {
        console.error('❌ Erreur calcul solde:', error);
    }
}

/**
 * Obtenir le montant total de l'achat
 */
function getMontantTotal() {
    try {
        const montantDisplay = document.getElementById('achat-montant-total-display').textContent;
        return parseFloat(montantDisplay.replace(/[^\d.-]/g, '')) || 0;
    } catch (error) {
        console.error('❌ Erreur récupération montant total:', error);
        return 0;
    }
}

/**
 * Formater une valeur en devise
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    }).format(amount);
}

/**
 * Valider les montants de paiement
 */
function validatePaymentAmounts() {
    try {
        const montantTotal = getMontantTotal();
        const montantPaye = parseFloat(document.getElementById('achat-montant-payé').value) || 0;
        const montantPayeInput = document.getElementById('achat-montant-payé');
        
        // Validation: le montant payé ne peut pas être négatif
        if (montantPaye < 0) {
            montantPayeInput.style.borderColor = '#ef4444'; // Rouge
            showNotification('Le montant payé ne peut pas être négatif', 'warning');
            return false;
        }
        
        // Validation: recommandation si montant payé > total
        if (montantPaye > montantTotal) {
            montantPayeInput.style.borderColor = '#eab308'; // Orange
            console.warn('⚠️ Montant payé supérieur au total');
        } else {
            montantPayeInput.style.borderColor = ''; // Reset
        }
        
        // Validation obligatoire selon le statut
        const statut = document.getElementById('achat-statut').value;
        const datePaiement = document.getElementById('achat-date-paiement').value;
        const compte = document.getElementById('achat-compte').value;
        
        if ((statut === 'paye' || statut === 'partiel') && (!montantPaye || !datePaiement || !compte)) {
            return false;
        }
        
        return true;
        
    } catch (error) {
        console.error('❌ Erreur validation montants:', error);
        return false;
    }
}

/**
 * Mettre à jour le texte d'aide contextuelle
 */
function updatePaymentHelpText() {
    try {
        const statut = document.getElementById('achat-statut').value;
        const helpElement = document.getElementById('payment-help');
        
        if (!helpElement) return;
        
        let helpText = '';
        
        switch (statut) {
            case 'non_paye':
                helpText = 'L\'achat n\'a pas encore été payé';
                break;
            case 'partiel':
                helpText = 'Paiement partiel. Le solde restant s\'affichera automatiquement';
                break;
            case 'paye':
                helpText = 'Paiement complet. Le montant doit égaler le total TTC';
                break;
            default:
                helpText = 'Sélectionnez un statut pour continuer';
        }
        
        helpElement.textContent = helpText;
        
    } catch (error) {
        console.error('❌ Erreur mise à jour aide:', error);
    }
}

/**
 * Synchroniser le montant total depuis l\'onglet 2
 */
function syncTotalAmount() {
    try {
        // Récupérer les valeurs des calculs financiers de l'onglet 2
        const montantTotalDisplay = document.getElementById('achat-ttc-calcule').textContent;
        const montantHTDisplay = document.getElementById('achat-montant-ht-calcule')?.textContent || '0,00 €';
        const tvaDisplay = document.getElementById('achat-tva-calcule').textContent;
        
        // Convertir et afficher dans l'onglet 3
        const total = parseFloat(montantTotalDisplay.replace(/[^\d.-]/g, '')) || 0;
        const ht = parseFloat(montantHTDisplay.replace(/[^\d.-]/g, '')) || 0;
        const tva = parseFloat(tvaDisplay.replace(/[^\d.-]/g, '')) || 0;
        
        document.getElementById('achat-montant-total-display').textContent = formatCurrency(total);
        document.getElementById('achat-montant-ht-display').textContent = formatCurrency(ht);
        document.getElementById('achat-tva-display').textContent = formatCurrency(tva);
        document.getElementById('achat-total-ttc-display').textContent = formatCurrency(total);
        
        // Recalculer le solde si des paiements sont définis
        calculatePaymentBalance();
        
        console.log('📊 Montant total synchronisé:', total);
        
    } catch (error) {
        console.error('❌ Erreur synchronisation montant:', error);
    }
}

/**
 * Initialiser l'onglet paiement lors de l'ouverture du modal
 */
function initializePaymentTab() {
    try {
        console.log('🎯 Initialisation onglet paiement...');
        
        // Synchroniser les montants depuis l'onglet 2
        syncTotalAmount();
        
        // Mettre à jour l'aide contextuelle
        updatePaymentHelpText();
        
        // Initialiser le statut de paiement (définit les champs requis)
        updatePaymentStatus();
        
        // Validation initiale
        validatePaymentAmounts();
        
        console.log('✅ Onglet paiement initialisé');
        
    } catch (error) {
        console.error('❌ Erreur initialisation onglet paiement:', error);
    }
}

/**
 * =====================================================
 * 🎨 STYLES CSS POUR ONGLET PAIEMENT
 * =====================================================
 */

// Ajouter les styles CSS pour l'onglet 3
const paymentStyles = document.createElement('style');
paymentStyles.textContent = `
/* === ONGLET PAIEMENT & ÉTAT === */

/* Carte de récapitulatif des montants */
.payment-summary-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.payment-summary-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.payment-summary-header h3 {
    margin: 0;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.total-amount span {
    font-size: 1.8rem;
    font-weight: bold;
    background: rgba(255,255,255,0.2);
    padding: 8px 16px;
    border-radius: 8px;
}

.payment-summary-details {
    background: rgba(255,255,255,0.1);
    border-radius: 8px;
    padding: 12px;
}

.summary-line {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 4px 0;
    font-size: 0.95rem;
}

.summary-line.total {
    border-top: 1px solid rgba(255,255,255,0.3);
    margin-top: 8px;
    padding-top: 8px;
    font-weight: bold;
}

/* Sections des paiements */
.payment-status-section,
.payment-details-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 16px;
}

.payment-status-section h4,
.payment-details-section h4,
.payment-history-section h4 {
    margin: 0 0 12px 0;
    color: #495057;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1rem;
}

/* Sélection de statut */
.status-select {
    font-weight: 500;
    padding: 8px 12px;
    border-radius: 6px;
    border: 2px solid #dee2e6;
    transition: all 0.2s ease;
}

.status-select:focus {
    border-color: #667eea;
    outline: none;
}

/* Input avec unité */
.input-with-unit {
    position: relative;
    display: flex;
    align-items: center;
}

.input-with-unit input {
    padding-right: 30px;
}

.input-with-unit .unit {
    position: absolute;
    right: 10px;
    color: #6c757d;
    font-weight: 500;
}

/* Texte d'aide */
.help-text {
    color: #6c757d;
    font-size: 0.85rem;
    margin-top: 4px;
    font-style: italic;
}

/* Carte de solde */
.balance-card {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.balance-display {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.balance-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    background: #f8f9fa;
    border-radius: 6px;
}

.balance-item.remainder {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
}

.balance-item .amount {
    font-weight: 600;
    font-family: 'Courier New', monospace;
}

/* Historique des paiements */
.payment-history-container {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 16px;
    min-height: 80px;
}

.no-history {
    text-align: center;
    color: #6c757d;
    padding: 20px;
}

.no-history i {
    font-size: 2rem;
    margin-bottom: 8px;
    opacity: 0.5;
}

.no-history p {
    margin: 0;
    font-style: italic;
}

/* Liste des paiements partiels */
.payment-history-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.payment-history-item {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.payment-history-info {
    flex: 1;
}

.payment-history-date {
    font-size: 0.9rem;
    color: #6c757d;
}

.payment-history-amount {
    font-weight: 600;
    color: #28a745;
}

.payment-history-actions {
    display: flex;
    gap: 8px;
}

/* Améliorations visuelles */
.form-group select,
.form-group input,
.form-group textarea {
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.form-group select:focus,
.form-group input:focus,
.form-group textarea:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* Animations */
.payment-summary-card {
    animation: slideInDown 0.5s ease-out;
}

@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .payment-summary-header {
        flex-direction: column;
        text-align: center;
        gap: 8px;
    }
    
    .balance-item {
        flex-direction: column;
        text-align: center;
        gap: 4px;
    }
    
    .balance-item .amount {
        font-size: 1.1rem;
    }
}
`;

// Ajouter les styles au document
document.head.appendChild(paymentStyles);