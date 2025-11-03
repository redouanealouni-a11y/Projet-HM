// main.js

// --- Fonctions Utilitaires de Sécurité ---
/**
 * Sanitize a string to prevent XSS attacks before inserting into HTML.
 * @param {string} str The string to sanitize.
 * @returns {string} The sanitized string.
 */
function sanitizeHTML(str) {
    if (str === null || str === undefined) {
        return '';
    }
    const temp = document.createElement('div');
    temp.textContent = String(str);
    return temp.innerHTML;
}

// Configuration de l'API avec détection automatique
let API_BASE = '/baba/api'; // Valeur par défaut

// Fonction pour détecter le bon chemin API
async function detectAndSetAPIPath() {
    const possiblePaths = [
        './api',
        'api',
        '../api',
        './test-repo-feature-comprehensive-improvements/api',
        './test-repo-feature-comprehensive-improvements/test-repo-feature-comprehensive-improvements/api'
    ];
    
    console.log('🔍 Détection du chemin API...');
    
    for (const path of possiblePaths) {
        try {
            const response = await fetch(`${path}/tiers.php`, { method: 'GET' });
            if (response.status !== 404) {
                console.log(`✅ Chemin API détecté: ${path}`);
                API_BASE = path;
                return path;
            }
        } catch (error) {
            // Continuer avec le chemin suivant
        }
    }
    
    console.warn('⚠️ Aucun chemin API valide détecté, utilisation du défaut:', API_BASE);
    return API_BASE;
}

// =====================================================
// 🛒 SECTION: ACHATS/DÉPENSES - SYSTÈME ORGANISÉ
// =====================================================
// Import du système restructuré pour les achats/dépenses
// Ce système remplace les fonctions dupliquées et organise le code

// Dynamically load the organized purchases system
function loadOrganizedPurchasesSystem() {
    console.log('🛒 Chargement du système organisé des achats/dépenses...');
    
    // Load the organized purchases modules
    const scripts = [
        'js/purchases/purchases-navigation.js',
        'js/purchases/purchases-modal.js', 
        'js/purchases/purchases-data.js',
        'js/purchases/purchases-validation.js',
        'js/purchases/purchases-save.js',
        'js/purchases/purchases-index.js'
    ];
    
    let loadedCount = 0;
    
    scripts.forEach(scriptSrc => {
        const script = document.createElement('script');
        script.src = scriptSrc;
        script.onload = () => {
            loadedCount++;
            console.log(`✅ Module chargé: ${scriptSrc}`);
            if (loadedCount === scripts.length) {
                console.log('🛒 Système organisé achats/dépenses complètement chargé');
            }
        };
        script.onerror = () => {
            console.warn(`⚠️ Erreur chargement: ${scriptSrc}`);
        };
        document.head.appendChild(script);
    });
}

// Charger le système au chargement de la page
document.addEventListener('DOMContentLoaded', loadOrganizedPurchasesSystem);

// Variables globales
let currentSection = 'dashboard';
let currentTiersType = 'client';
let currentAccountType = 'caisse';
let uploadedFiles = [];
let editingId = null;
let charts = {};
let transactionFiles = []; // Tableau pour stocker tous les fichiers joints cumulativement
let appData = {
    transactions: [],
    comptes: [],
    categories: [],
    clients: [],
    fournisseurs: [],
    settings: {}
};

// Initialisation de l'application
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initialisation de l\'application PHP/PostgreSQL...');
    
    // Définir la date actuelle
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('transaction-date').value = today;
    
    // Initialiser l'application
    initializeApp();
});

async function initializeApp() {
    try {
        showConnectionStatus('loading', 'Chargement...');
        
        // Détecter le bon chemin API en premier
        await detectAndSetAPIPath();
        
        // Charger les données de base
        await loadAllData();
        
        // Initialiser l'interface
        updateAllDisplays();
        showSection('dashboard');
        
        showConnectionStatus('success', 'Connecté');
        console.log('Application initialisée avec succès');
        
    } catch (error) {
        console.error('Erreur d\'initialisation:', error);
        showConnectionStatus('error', 'Erreur de connexion');
        
        // Si l'erreur est liée au chemin API, afficher un message spécifique
        if (error.message.includes('404') || error.message.includes('fetch')) {
            showNotification('Erreur: API non trouvée. Vérifiez la structure des fichiers.', 'error');
        } else {
            showNotification('Erreur de connexion à la base de données', 'error');
        }
    }
}

// Gestion du statut de connexion
function showConnectionStatus(status, message) {
    const statusElement = document.getElementById('connection-status');
    const textElement = document.getElementById('status-text');
    
    statusElement.className = 'px-3 py-1 rounded-full text-sm';
    
    switch (status) {
        case 'success':
            statusElement.classList.add('bg-green-100', 'text-green-800');
            break;
        case 'error':
            statusElement.classList.add('bg-red-100', 'text-red-800');
            break;
        case 'loading':
            statusElement.classList.add('bg-yellow-100', 'text-yellow-800');
            break;
        default:
            statusElement.classList.add('bg-gray-100', 'text-gray-800');
    }
    
    textElement.textContent = message;
}

// Fonctions API
async function apiCall(endpoint, options = {}) {
    try {
        const url = `${API_BASE}${endpoint}`;
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
            },
        };
        
        const finalOptions = { ...defaultOptions, ...options };
        
        if (finalOptions.body && typeof finalOptions.body === 'object') {
            finalOptions.body = JSON.stringify(finalOptions.body);
        }
        
        const response = await fetch(url, finalOptions);
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || `Erreur HTTP: ${response.status}`);
        }
        
        const text = await response.text();
        // Handle successful but empty responses (e.g., for DELETE)
        if (!text) {
            return { success: true };
        }

        const data = JSON.parse(text);
        if (data.error === true || data.success === false) {
            throw new Error(data.message || 'Réponse API non réussie');
        }

        return data;
        
    } catch (error) {
        console.error(`Erreur API pour ${endpoint}:`, error);
        throw error;
    }
}

// Chargement des données
async function loadAllData() {
    try {
        const [
            settingsResponse,
            categoriesResponse,
            comptesResponse,
            clientsResponse,
            fournisseursResponse
        ] = await Promise.all([
            apiCall('/settings.php'),
            apiCall('/categories.php'),
            apiCall('/comptes.php'),
            apiCall('/tiers.php?type=client'),
            apiCall('/tiers.php?type=fournisseur')
        ]);
        
        appData.settings = settingsResponse.data || {};
        appData.categories = categoriesResponse.data || [];
        appData.comptes = comptesResponse.data || [];
        appData.clients = clientsResponse.data || [];
        appData.fournisseurs = fournisseursResponse.data || [];
        
        console.log('Données chargées:', appData);
        
    } catch (error) {
        console.error('Erreur lors du chargement des données:', error);
        throw error;
    }
}

async function loadTransactions(filters = {}) {
    try {
        const params = new URLSearchParams();
        Object.keys(filters).forEach(key => {
            if (filters[key]) {
                params.append(key, filters[key]);
            }
        });
        
        const response = await apiCall(`/transactions.php?${params.toString()}`);
        appData.transactions = response.data || [];
        return appData.transactions;
        
    } catch (error) {
        console.error('Erreur lors du chargement des transactions:', error);
        // Améliorer le message d'erreur pour le débogage
        const errorMessage = error.message || error.toString() || 'Erreur inconnue lors du chargement des transactions';
        throw new Error(errorMessage);
    }
}

// Navigation
function showSection(section) {
    document.querySelectorAll('.section').forEach(s => s.style.display = 'none');
    document.getElementById(section + '-section').style.display = 'block';
    
    document.querySelectorAll('.sidebar-item').forEach(item => {
        item.classList.remove('sidebar-active');
    });
    const activeItem = document.querySelector(`[onclick="showSection('${section}')"]`);
    if (activeItem) activeItem.classList.add('sidebar-active');
    
    const titles = {
        dashboard: 'Tableau de bord',
        transactions: 'Transactions',
        clients: 'Gestion des Clients',
        fournisseurs: 'Gestion des Fournisseurs',
        banque: 'Comptes Bancaires',
        caisse: 'Caisses',
        achats: 'Achats et Dépenses',
        rapports: 'Rapports',
        parametres: 'Paramètres'
    };
    
    document.getElementById('page-title').textContent = titles[section];
    currentSection = section;
    
    if (section === 'dashboard') {
        updateDashboard();
    } else if (section === 'transactions') {
        updateTransactionsDisplay();
    } else if (section === 'clients') {
        updateClientsDisplay();
    } else if (section === 'fournisseurs') {
        updateFournisseursDisplay();
    } else if (section === 'banque') {
        updateBanqueDisplay();
    } else if (section === 'caisse') {
        updateCaisseDisplay();
    } else if (section === 'achats') {
        updateAchatsDisplay();
    } else if (section === 'rapports') {
        updateRapportsDisplay();
    } else if (section === 'parametres') {
        updateParametresDisplay();
    }
}

// Mise à jour des affichages
function updateAllDisplays() {
    updateAccountSelects();
    updateTiersSelects();
    updateCategorySelects();
}

function updateAccountSelects() {
    const selects = [
        'transaction-account',
        'filter-account',
        'transfer-from-account',
        'transfer-to-account',
        'filter-caisse-account',
        'caisse-operation-caisse'
    ];
    
    selects.forEach(selectId => {
        const select = document.getElementById(selectId);
        if (select) {
            const currentValue = select.value;
            select.innerHTML = '<option value="">Sélectionner un compte</option>';
            
            // Pour les éléments liés aux caisses, ne montrer que les comptes de type 'caisse'
            let comptesToShow = appData.comptes;
            if (selectId === 'filter-caisse-account' || selectId === 'caisse-operation-caisse') {
                comptesToShow = appData.comptes.filter(compte => compte.type === 'caisse');
            }
            
            comptesToShow.forEach(compte => {
                const option = document.createElement('option');
                option.value = compte.id;
                option.textContent = `${compte.name} (${formatCurrency(compte.balance)})`;
                select.appendChild(option);
            });
            
            if (currentValue) {
                select.value = currentValue;
            }
        }
    });
}

function updateTiersSelects() {
    const selects = [
        'transaction-tiers',
        'filter-tiers',
        'filter-caisse-tiers',
        'caisse-operation-tiers'
    ];
    
    selects.forEach(selectId => {
        const select = document.getElementById(selectId);
        if (select) {
            const currentValue = select.value;
            const defaultText = (selectId === 'filter-tiers' || selectId === 'filter-caisse-tiers') ? 'Tous les tiers' : 'Aucun tiers';
            select.innerHTML = `<option value="">${defaultText}</option>`;
            
            [...appData.clients, ...appData.fournisseurs].forEach(tiers => {
                const option = document.createElement('option');
                option.value = tiers.id;
                option.textContent = tiers.raison_sociale;
                select.appendChild(option);
            });
            
            if (currentValue) {
                select.value = currentValue;
            }
        }
    });
}

function updateCategorySelects() {
    const selects = [
        'transaction-category',
        'caisse-operation-category',
        'filter-category',
        'filter-caisse-category'
    ];
    
    selects.forEach(selectId => {
        const select = document.getElementById(selectId);
        if (select) {
            const currentValue = select.value;
            const defaultText = (selectId === 'filter-category' || selectId === 'filter-caisse-category') ? 'Toutes les catégories' : 'Aucune catégorie';
            select.innerHTML = `<option value="">${defaultText}</option>`;
            
            appData.categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                select.appendChild(option);
            });
            
            if (currentValue) {
                select.value = currentValue;
            }
        }
    });
}

// Dashboard
// Dashboard amélioré avec gestion des erreurs
async function updateDashboard() {
    try {
        console.log('🔄 Mise à jour du dashboard...');
        
        // Recharger les données de base avec timeout
        try {
            await Promise.race([
                loadAllData(),
                new Promise((_, reject) => setTimeout(() => reject(new Error('Timeout')), 10000))
            ]);
        } catch (error) {
            console.warn('⚠️ Timeout ou erreur lors du chargement des données de base:', error);
            showNotification('Chargement partiel des données', 'warning');
        }
        
        // Charger les statistiques avec gestion d'erreur et valeurs par défaut
        let stats = { total_recettes: 0, total_depenses: 0, total_transactions: 0 };
        try {
            const statsResponse = await Promise.race([
                apiCall('/transactions.php?action=stats'),
                new Promise((_, reject) => setTimeout(() => reject(new Error('Timeout stats')), 5000))
            ]);
            
            if (statsResponse && statsResponse.data) {
                stats = statsResponse.data;
                
                // Valider les données reçues
                stats.total_recettes = isNaN(stats.total_recettes) ? 0 : Math.max(0, parseFloat(stats.total_recettes));
                stats.total_depenses = isNaN(stats.total_depenses) ? 0 : Math.max(0, parseFloat(stats.total_depenses));
                stats.total_transactions = isNaN(stats.total_transactions) ? 0 : Math.max(0, parseInt(stats.total_transactions));
                
                // Détecter des valeurs anormales
                if (stats.total_recettes > 100000000 || stats.total_depenses > 100000000) {
                    console.warn('⚠️ Valeurs anormalement élevées détectées dans les stats');
                    showNotification('Données de test détectées - Utilisez le nettoyage', 'warning');
                }
                
                console.log('📊 Stats chargées et validées:', stats);
            }
        } catch (error) {
            console.error('❌ Erreur stats:', error);
            showNotification('Impossible de charger les statistiques', 'warning');
        }
        
        // Charger les transactions récentes avec gestion d'erreur
        let recentTransactions = [];
        try {
            const transactionsResponse = await Promise.race([
                apiCall('/transactions.php?limit=10'),
                new Promise((_, reject) => setTimeout(() => reject(new Error('Timeout transactions')), 5000))
            ]);
            
            if (transactionsResponse && transactionsResponse.data) {
                recentTransactions = Array.isArray(transactionsResponse.data) ? transactionsResponse.data : [];
                console.log('📋 Transactions récentes chargées:', recentTransactions.length);
            }
        } catch (error) {
            console.error('❌ Erreur transactions:', error);
            showNotification('Impossible de charger les transactions récentes', 'warning');
        }
        
        // Calculer le solde total avec validation
        let totalBalance = 0;
        if (Array.isArray(appData.comptes)) {
            totalBalance = appData.comptes.reduce((sum, compte) => {
                const balance = parseFloat(compte.balance || 0);
                return sum + (isNaN(balance) ? 0 : balance);
            }, 0);
        }
        
        // Détecter un solde anormalement élevé
        if (totalBalance > 100000000) {
            console.warn('⚠️ Solde total anormalement élevé:', totalBalance);
            showNotification('Solde anormalement élevé - Vérifiez vos données', 'warning');
        }
        
        console.log('💰 Solde total calculé:', totalBalance);
        
        // Mettre à jour l'interface avec validation des éléments DOM
        try {
            const totalBalanceEl = document.getElementById('total-balance');
            if (totalBalanceEl) {
                totalBalanceEl.textContent = formatCurrency(totalBalance);
                totalBalanceEl.className = `text-2xl font-semibold ${totalBalance >= 0 ? 'text-green-600' : 'text-red-600'}`;
            }
            
            const totalRecettesEl = document.getElementById('total-recettes');
            if (totalRecettesEl) {
                totalRecettesEl.textContent = formatCurrency(stats.total_recettes);
            }
            
            const totalDepensesEl = document.getElementById('total-depenses');
            if (totalDepensesEl) {
                totalDepensesEl.textContent = formatCurrency(stats.total_depenses);
            }
            
            const totalTransactionsEl = document.getElementById('total-transactions');
            if (totalTransactionsEl) {
                totalTransactionsEl.textContent = stats.total_transactions.toString();
            }
        } catch (error) {
            console.error('❌ Erreur mise à jour DOM:', error);
        }
        
        // Mettre à jour les transactions récentes
        try {
            updateRecentTransactionsTable(recentTransactions);
        } catch (error) {
            console.error('❌ Erreur tableau transactions:', error);
        }
        
        // Mettre à jour les graphiques avec gestion d'erreur
        try {
            // Valider les données pour les graphiques
            const chartStats = {
                total_recettes: Math.min(stats.total_recettes, 10000000), // Limiter à 10M pour l'affichage
                total_depenses: Math.min(stats.total_depenses, 10000000),
                total_transactions: stats.total_transactions
            };
            
            const chartComptes = appData.comptes.map(compte => ({
                ...compte,
                balance: Math.min(Math.abs(parseFloat(compte.balance || 0)), 10000000)
            }));
            
            updateCharts(chartStats, chartComptes);
            console.log('📈 Graphiques mis à jour');
        } catch (error) {
            console.error('❌ Erreur graphiques:', error);
        }
        
        console.log('✅ Dashboard mis à jour avec succès');
        
    } catch (error) {
        console.error('❌ Erreur globale dashboard:', error);
        showNotification('Erreur lors du chargement du dashboard: ' + error.message, 'error');
    }
}

// Fonction améliorée de formatage des devises avec gestion des gros montants
function formatCurrency(amount) {
    // Convertir et valider
    const numAmount = parseFloat(amount) || 0;
    
    // Pour les très gros montants, utiliser une notation simplifiée
    if (Math.abs(numAmount) >= 1000000000) {
        return (numAmount / 1000000000).toFixed(1) + ' Mrd €';
    } else if (Math.abs(numAmount) >= 1000000) {
        return (numAmount / 1000000).toFixed(1) + ' M €';
    } else {
        return new Intl.NumberFormat('fr-FR', {
            style: 'currency',
            currency: 'EUR',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(numAmount);
    }
}

// Fonction spécialisée pour l'affichage des montants dans les tableaux
// Masque le signe négatif pour tous les types de virements (débit et crédit)
function formatCurrencyForDisplay(transaction) {
    // Pour les virements (débit et crédit), afficher sans le signe
    if (transaction.type === 'virement_debit' || transaction.type === 'virement_credit') {
        const absAmount = Math.abs(parseFloat(transaction.amount) || 0);
        return formatCurrency(absAmount);
    }
    
    // Pour tous les autres types, afficher normalement
    return formatCurrency(transaction.amount);
}


function updateRecentTransactionsTable(transactions) {
    const tbody = document.getElementById('recent-transactions');
    
    if (!transactions || transactions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-gray-500">Aucune transaction récente</td></tr>';
        return;
    }
    
    tbody.innerHTML = transactions.map(transaction => `
        <tr>
            <td class="px-4 py-2">${sanitizeHTML(formatDate(transaction.date))}</td>
            <td class="px-4 py-2">
                <span class="px-2 py-1 rounded text-xs ${sanitizeHTML(getTypeClass(transaction.type))}">
                    ${sanitizeHTML(getTypeLabel(transaction.type))}
                </span>
            </td>
            <td class="px-4 py-2">${sanitizeHTML(transaction.description)}</td>
            <td class="px-4 py-2">${sanitizeHTML(transaction.account_name || 'N/A')}</td>
            <td class="px-4 py-2">${sanitizeHTML(transaction.tiers_name || '-')}</td>
            <td class="px-4 py-2 text-right ${sanitizeHTML(getAmountClass(transaction.type))}">
                ${sanitizeHTML(formatCurrencyForDisplay(transaction))}
            </td>
            <td class="px-4 py-2 text-center no-print">
                <button onclick="viewTransactionDetails(${JSON.stringify(transaction).replace(/"/g, '&quot;')})" class="text-blue-600 hover:text-blue-800 p-1 rounded transition-colors" title="Visualiser les détails">
                    <i class="fas fa-eye"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

// Transactions
async function updateTransactionsDisplay() {
    try {
        const filters = getTransactionFilters();
        const transactions = await loadTransactions(filters);
        updateTransactionsTable(transactions);
        
    } catch (error) {
        console.error('Erreur lors de la mise à jour des transactions:', error);
        const errorMessage = error.message || error.toString() || 'Erreur inconnue';
        showNotification('Erreur lors du chargement des transactions: ' + errorMessage, 'error');
    }
}

function getTransactionFilters() {
    try {
        return {
            search: document.getElementById('search-transactions')?.value?.trim() || '',
            type: document.getElementById('filter-type')?.value || '',
            account_id: document.getElementById('filter-account')?.value || '',
            tiers_id: document.getElementById('filter-tiers')?.value || '',
            category_id: document.getElementById('filter-category')?.value || '',
            month: document.getElementById('filter-month')?.value || ''
        };
    } catch (error) {
        console.warn('Erreur lors de la récupération des filtres:', error);
        return { search: '', type: '', account_id: '', tiers_id: '', category_id: '', month: '' };
    }
}

function applyTransactionFilters() {
    updateTransactionsDisplay();
}

// Variable pour stocker le timer de débounce
let searchDebounceTimer = null;

/**
 * Fonction de débounce pour la recherche en temps réel
 * Attend 300ms après que l'utilisateur arrête de taper avant de lancer la recherche
 */
function debouncedApplyTransactionFilters() {
    // Annuler le timer précédent s'il existe
    if (searchDebounceTimer) {
        clearTimeout(searchDebounceTimer);
    }
    
    // Créer un nouveau timer
    searchDebounceTimer = setTimeout(() => {
        applyTransactionFilters();
    }, 300); // Attendre 300ms après la dernière frappe
}

function updateTransactionsTable(transactions) {
    const tbody = document.getElementById('transactions-table');
    
    if (!transactions || transactions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-gray-500">Aucune transaction trouvée</td></tr>';
        return;
    }
    
    tbody.innerHTML = transactions.map(transaction => `
        <tr>
            <td class="px-4 py-3">${sanitizeHTML(formatDate(transaction.date))}</td>
            <td class="px-4 py-3">
                <span class="px-2 py-1 rounded text-xs ${sanitizeHTML(getTypeClass(transaction.type))}">
                    ${sanitizeHTML(getTypeLabel(transaction.type))}
                </span>
            </td>
            <td class="px-4 py-3">${sanitizeHTML(transaction.description)}</td>
            <td class="px-4 py-3">${sanitizeHTML(transaction.account_name || 'N/A')}</td>
            <td class="px-4 py-3">${sanitizeHTML(transaction.tiers_name || '-')}</td>
            <td class="px-4 py-3">${sanitizeHTML(transaction.category_name || '-')}</td>
            <td class="px-4 py-3 text-right ${sanitizeHTML(getAmountClass(transaction.type))}">
                ${sanitizeHTML(formatCurrencyForDisplay(transaction))}
            </td>
            <td class="px-4 py-3 text-right">
                ${transaction.balance_after ? sanitizeHTML(formatCurrency(transaction.balance_after)) : '-'}
            </td>
            <td class="px-4 py-3 text-center no-print">
                <div class="flex justify-center space-x-1">
                    <button onclick="viewTransactionDetails(${JSON.stringify(transaction).replace(/"/g, '&quot;')})" class="text-blue-600 hover:text-blue-800 p-1 rounded transition-colors" title="Visualiser les détails">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button onclick="editTransaction('${transaction.id}')" class="text-green-600 hover:text-green-800 p-1 rounded transition-colors" title="Modifier">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteTransaction('${transaction.id}')" class="text-red-600 hover:text-red-800 p-1 rounded transition-colors" title="Supprimer">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Gestion des transactions
function openTransactionModal() {
    editingId = null;
    transactionFiles = []; // Réinitialiser la liste des fichiers
    document.getElementById('transactionForm').reset();
    document.querySelector('#transactionModal h3').innerHTML = '<i class="fas fa-exchange-alt mr-2 text-blue-600"></i>Nouvelle Transaction';
    document.querySelector('#transactionModal button[onclick="saveTransaction(false)"]').innerHTML = '<i class="fas fa-save mr-2"></i>Valider et fermer';
    document.querySelector('#transactionModal button[onclick="saveTransaction(true)"]').innerHTML = '<i class="fas fa-plus mr-2"></i>Ajouter et continuer';
    document.querySelector('#transactionModal button[onclick="saveTransaction(true)"]').style.display = 'inline-block';

    const today = new Date().toISOString().split('T')[0];
    document.getElementById('transaction-date').value = today;

    // Initialiser le premier onglet (Informations principales)
    switchTransactionTab('main-info');
    
    // Initialiser le modal et le récapitulatif
    initTransactionModal();
    
    // Afficher la liste vide de documents
    renderFilesList();

    document.getElementById('transactionModal').style.display = 'block';
}

async function editTransaction(id) {
    try {
        const response = await apiCall(`/transactions.php?id=${id}`);
        const transaction = response.data;

        if (!transaction) {
            showNotification('Transaction non trouvée.', 'error');
            return;
        }

        // 🔍 DÉTECTION: Si c'est un virement, ouvrir le modal de virement
        if (transaction.type === 'virement_debit' || transaction.type === 'virement_credit') {
            console.log('🔄 Détection d\'un virement, ouverture du transferModal...');
            editTransfer(id, transaction);
            return;
        }

        editingId = id;

        // Populate the form
        document.getElementById('transaction-type').value = transaction.type;
        document.getElementById('transaction-account').value = transaction.account_id;
        document.getElementById('transaction-description').value = transaction.description;
        document.getElementById('transaction-amount').value = transaction.amount;
        document.getElementById('transaction-date').value = transaction.date;
        document.getElementById('transaction-category').value = transaction.category_id || '';
        document.getElementById('transaction-tiers').value = transaction.tiers_id || '';
        document.getElementById('transaction-reference').value = transaction.reference || '';
        document.getElementById('transaction-notes').value = transaction.notes || '';
        document.getElementById('transaction-payment-method').value = transaction.payment_method || '';
        document.getElementById('transaction-bank-status').value = transaction.bank_status || '';
        document.getElementById('transaction-value-date').value = transaction.value_date || '';
        document.getElementById('transaction-due-date').value = transaction.due_date || '';
        document.getElementById('transaction-effective-date').value = transaction.effective_date || '';
        document.getElementById('transaction-balance-impact').value = transaction.balance_impact || '';
        document.getElementById('transaction-bank-notes').value = transaction.bank_notes || '';
        document.getElementById('transaction-general-comments').value = transaction.general_comments || '';

        // Réinitialiser la liste des fichiers pour cette transaction
        transactionFiles = [];
        
        // Afficher les documents existants si disponibles
        if (transaction.documents && transaction.documents.length > 0) {
            console.log('Documents existants:', transaction.documents);
            renderExistingDocuments(transaction.documents);
        } else {
            // Afficher la liste vide si aucun document
            renderFilesList();
        }

        // Change modal title and button text for editing
        document.querySelector('#transactionModal h3').textContent = 'Modifier la Transaction';
        document.querySelector('#transactionModal button[onclick="saveTransaction(false)"]').textContent = 'Enregistrer les modifications';
        document.querySelector('#transactionModal button[onclick="saveTransaction(true)"]').style.display = 'none'; // Hide "add and continue"

        // Initialiser le premier onglet (Informations principales)
        switchTransactionTab('main-info');
        
        // Initialiser le modal et le récapitulatif
        initTransactionModal();

        // Open the modal
        document.getElementById('transactionModal').style.display = 'block';

    } catch (error) {
        console.error('Erreur lors du chargement de la transaction pour modification:', error);
        showNotification(error.message, 'error');
    }
}

function closeTransactionModal() {
    transactionFiles = []; // Réinitialiser la liste des fichiers à la fermeture
    document.getElementById('transactionModal').style.display = 'none';
}

function resetTransactionForm() {
    document.getElementById('transactionForm').reset();
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('transaction-date').value = today;
}

/**
 * Nettoie et parse la valeur du champ balance_impact
 * Exemple: "+987.00 €" -> 987.00
 */
function parseBalanceImpact(value) {
    if (!value || value === '') {
        return null;
    }
    
    // Enlever les espaces, le symbole € et le symbole +
    const cleanValue = value.replace(/[\s€+]/g, '');
    
    // Convertir en nombre
    const numValue = parseFloat(cleanValue);
    
    // Vérifier que c'est un nombre valide
    if (isNaN(numValue)) {
        return null;
    }
    
    return numValue;
}

async function saveTransaction(continueAdding = false) {
    try {
        const formData = {
            type: document.getElementById('transaction-type').value,
            description: document.getElementById('transaction-description').value,
            amount: parseFloat(document.getElementById('transaction-amount').value),
            date: document.getElementById('transaction-date').value,
            account_id: document.getElementById('transaction-account').value,
            category_id: document.getElementById('transaction-category').value || null,
            tiers_id: document.getElementById('transaction-tiers').value || null,
            reference: document.getElementById('transaction-reference').value || null,
            notes: document.getElementById('transaction-notes').value || null,
            payment_method: document.getElementById('transaction-payment-method').value || null,
            bank_status: document.getElementById('transaction-bank-status').value || null,
            value_date: document.getElementById('transaction-value-date').value || null,
            due_date: document.getElementById('transaction-due-date').value || null,
            effective_date: document.getElementById('transaction-effective-date').value || null,
            balance_impact: parseBalanceImpact(document.getElementById('transaction-balance-impact').value),
            bank_notes: document.getElementById('transaction-bank-notes').value || null,
            general_comments: document.getElementById('transaction-general-comments').value || null
        };

        if (!formData.type || !formData.description || !formData.amount || !formData.account_id) {
            showNotification('Veuillez remplir tous les champs obligatoires', 'error');
            return;
        }

        if (formData.amount <= 0) {
            showNotification('Le montant doit être positif', 'error');
            return;
        }

        const method = editingId ? 'PUT' : 'POST';
        const url = editingId ? `/transactions.php?id=${editingId}` : '/transactions.php';

        const response = await apiCall(url, {
            method: method,
            body: formData
        });

        // Récupérer l'ID de la transaction créée ou mise à jour
        const transactionId = response.data.id;

        // Uploader les fichiers si il y en a
        if (transactionFiles.length > 0) {
            await uploadTransactionDocuments(transactionId);
        }

        const message = editingId ? 'Transaction mise à jour avec succès' : 'Transaction enregistrée avec succès';
        showNotification(message, 'success');

        await loadAllData();
        await updateTransactionsDisplay(); // Always update the main transaction list
        updateAllDisplays();

        if (currentSection === 'dashboard') {
            updateDashboard();
        }

        if (continueAdding && !editingId) {
            openTransactionModal(); // Reset for new entry
        } else {
            closeTransactionModal();
        }

    } catch (error) {
        console.error('Erreur lors de l\'enregistrement:', error);
        showNotification(error.message, 'error');
    }
}

// Fonction pour uploader les documents associés à une transaction
async function uploadTransactionDocuments(transactionId) {
    try {
        console.log(`📤 Upload de ${transactionFiles.length} document(s) pour la transaction ${transactionId}`);
        
        for (const file of transactionFiles) {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('transaction_id', transactionId);

            const response = await fetch(API_BASE + '/upload_document.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                const errorMsg = errorData.message || errorData.error || `Erreur HTTP ${response.status}`;
                throw new Error(`Erreur lors de l'upload de ${file.name}: ${errorMsg}`);
            }

            const result = await response.json();
            console.log(`✅ Document ${file.name} uploadé avec succès:`, result);
        }
        
        console.log('✅ Tous les documents ont été uploadés avec succès');
        
    } catch (error) {
        console.error('❌ Erreur lors de l\'upload des documents:', error);
        showNotification(`Erreur lors de l'upload des documents: ${error.message}`, 'error');
        throw error; // Re-throw pour que l'appelant sache qu'il y a eu une erreur
    }
}

async function deleteTransaction(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette transaction ?')) {
        return;
    }

    try {
        await apiCall(`/transactions.php?id=${id}`, {
            method: 'DELETE'
        });
        
        showNotification('Transaction supprimée avec succès', 'success');
        
        await loadAllData();
        updateTransactionsDisplay();
        
        if (currentSection === 'dashboard') {
            updateDashboard();
        }
        
    } catch (error) {
        console.error('Erreur lors de la suppression:', error);
        showNotification(error.message, 'error');
    }
}

// Gestion des virements
let transferFiles = []; // Tableau pour stocker les fichiers du virement

function openTransferModal() {
    // ✨ Réinitialiser le mode modification
    editingId = null;
    
    transferFiles = []; // Réinitialiser la liste des fichiers
    document.getElementById('transferForm').reset();
    
    // Initialiser la date du jour
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('transfer-date').value = today;
    
    // 🏷️ Définir le titre du modal
    const modalTitle = document.querySelector('#transferModal h3');
    if (modalTitle) {
        modalTitle.textContent = 'Nouveau Virement de Fonds';
    }
    
    // Initialiser le premier onglet
    switchTransferTab('main-info');
    
    // Initialiser le modal et le récapitulatif
    initTransferModal();
    
    // Afficher la liste vide de fichiers
    renderTransferFilesList();
    
    document.getElementById('transferModal').style.display = 'block';
    updateAccountSelects();
}

function closeTransferModal() {
    // ✨ Réinitialiser le mode modification
    editingId = null;
    
    transferFiles = []; // Réinitialiser à la fermeture
    document.getElementById('transferModal').style.display = 'none';
    document.getElementById('transferForm').reset();
}

// ✨ NOUVELLE FONCTION: Modifier un virement existant
async function editTransfer(id, transaction) {
    try {
        console.log('📝 Chargement du virement pour modification:', id);
        
        // Si transaction n'est pas fournie, la charger
        if (!transaction) {
            const response = await apiCall(`/transactions.php?id=${id}`);
            transaction = response.data;
            
            if (!transaction) {
                showNotification('Virement non trouvé.', 'error');
                return;
            }
        }
        
        editingId = id;
        
        // 🎯 Stocker la transaction actuelle pour saveTransfer()
        currentTransaction = transaction;
        
        // 🔍 CORRECTION SIMPLIFIÉE: Utiliser d'abord les données locales
        console.log('🔄 Recherche des transactions liées avec transfer_ref:', transaction.transfer_ref);
        
        // D'abord, chercher dans les données locales déjà chargées
        let linkedTransactions = appData.transactions.filter(t => 
            t.transfer_ref === transaction.transfer_ref && t.id !== transaction.id
        );
        
        console.log('📊 Transactions liées trouvées en mémoire locale:', linkedTransactions.length);
        console.log('📊 Transactions locales:', linkedTransactions.map(t => ({ id: t.id, type: t.type, account_id: t.account_id })));
        
        // Si pas trouvé localement, essayer l'API (mais ce ne devrait pas être nécessaire)
        if (linkedTransactions.length === 0) {
            console.log('🔄 Aucune transaction liée en mémoire, tentative via API...');
            try {
                const linkedResponse = await apiCall(`/transactions.php?transfer_ref=${transaction.transfer_ref}`);
                console.log('📡 Réponse API pour transactions liées:', linkedResponse);
                
                if (linkedResponse.data && Array.isArray(linkedResponse.data)) {
                    linkedTransactions = linkedResponse.data;
                    console.log('✅ Transactions liées récupérées via API:', linkedTransactions.length);
                    console.log('📊 Transactions API:', linkedTransactions.map(t => ({ id: t.id, type: t.type, account_id: t.account_id })));
                }
            } catch (error) {
                console.warn('⚠️ Échec API, mais on continue avec les données locales:', error);
            }
        }
        
        // 🔍 Déterminer les comptes source et destination - VERSION SIMPLIFIÉE
        let fromAccountId, toAccountId;
        
        console.log('🔍 Détermination des comptes pour transaction:', transaction.type, transaction.id);
        
        // Logique simplifiée : chercher la transaction liée dans toutes les données locales
        const allRelatedTransactions = appData.transactions.filter(t => 
            t.transfer_ref === transaction.transfer_ref && t.id !== transaction.id
        );
        
        console.log('📊 Toutes les transactions liées (incluant celle en cours):', 
            [transaction, ...allRelatedTransactions].map(t => ({ id: t.id, type: t.type, account_id: t.account_id }))
        );
        
        if (transaction.type === 'virement_debit') {
            // Virement DÉBIT : ce compte = source, l'autre compte = destination
            fromAccountId = transaction.account_id;
            
            // Trouver la transaction crédit liée
            const creditTransaction = allRelatedTransactions.find(t => t.type === 'virement_credit') 
                || appData.transactions.find(t => t.transfer_ref === transaction.transfer_ref && t.type === 'virement_credit');
            
            if (creditTransaction) {
                toAccountId = creditTransaction.account_id;
                console.log('✅ Virement DÉBIT: Source =', fromAccountId, ', Destination =', toAccountId);
            } else {
                console.log('❌ Virement DÉBIT: Transaction crédit non trouvée!');
                console.log('📊 Toutes les transactions avec ce transfer_ref:');
                appData.transactions.filter(t => t.transfer_ref === transaction.transfer_ref)
                    .forEach(t => console.log(`  - ${t.type}: account_id=${t.account_id}, id=${t.id}`));
            }
            
        } else if (transaction.type === 'virement_credit') {
            // Virement CRÉDIT : ce compte = destination, l'autre compte = source
            toAccountId = transaction.account_id;
            
            // Trouver la transaction débit liée
            const debitTransaction = allRelatedTransactions.find(t => t.type === 'virement_debit') 
                || appData.transactions.find(t => t.transfer_ref === transaction.transfer_ref && t.type === 'virement_debit');
            
            if (debitTransaction) {
                fromAccountId = debitTransaction.account_id;
                console.log('✅ Virement CRÉDIT: Source =', fromAccountId, ', Destination =', toAccountId);
            } else {
                console.log('❌ Virement CRÉDIT: Transaction débit non trouvée!');
                console.log('📊 Toutes les transactions avec ce transfer_ref:');
                appData.transactions.filter(t => t.transfer_ref === transaction.transfer_ref)
                    .forEach(t => console.log(`  - ${t.type}: account_id=${t.account_id}, id=${t.id}`));
            }
        }
        
        console.log('📊 Comptes finaux: Source =', fromAccountId, ', Destination =', toAccountId);
        
        // 🔍 SOLUTION DE SECOURS: Si un compte manque, utiliser des valeurs par défaut intelligentes
        if (!fromAccountId || !toAccountId) {
            console.warn('⚠️ COMPTES MANQUANTS - Utilisation de la solution de secours');
            
            // Diagnostic détaillé
            console.log('🔍 Diagnostic complet:');
            console.log('- Transaction courante:', { id: transaction.id, type: transaction.type, account_id: transaction.account_id });
            console.log('- Transfer_ref:', transaction.transfer_ref);
            console.log('- fromAccountId trouvé:', fromAccountId);
            console.log('- toAccountId trouvé:', toAccountId);
            
            // Pour les virements, on peut au moins présélectionner le compte de la transaction courante
            if (transaction.type === 'virement_debit') {
                // Pour un virement débit, on knows le compte source
                if (!fromAccountId) fromAccountId = transaction.account_id;
                console.log('🔧 Virement DÉBIT: Utilisation du compte courant comme source');
            } else if (transaction.type === 'virement_credit') {
                // Pour un virement crédit, on knows le compte destination
                if (!toAccountId) toAccountId = transaction.account_id;
                console.log('🔧 Virement CRÉDIT: Utilisation du compte courant comme destination');
            }
            
            // Si toujours des comptes manquants, alerter
            if (!fromAccountId || !toAccountId) {
                console.error('❌ ERREUR: Impossible de déterminer les comptes même avec la solution de secours');
                showNotification('Erreur: Virement incomplet. Contactez l\'administrateur.', 'error');
                return;
            } else {
                console.log('✅ Solution de secours appliquée avec succès');
            }
        }
        
        console.log('✅ Comptes déterminés avec succès:', { fromAccountId, toAccountId });
        
        // ✨ CORRECTION: S'assurer que les comptes sont chargés avant la présélection
        updateAccountSelects();
        
        // Remplir le formulaire de virement
        console.log('🔄 Présélection des comptes dans le formulaire...');
        
        // Présélection avec vérifications
        const fromSelect = document.getElementById('transfer-from-account');
        const toSelect = document.getElementById('transfer-to-account');
        
        if (fromAccountId) {
            fromSelect.value = fromAccountId;
            console.log('✅ Compte source:', fromAccountId, '-> select:', fromSelect.value);
            if (fromSelect.value !== fromAccountId) {
                console.warn('⚠️ Compte source non trouvé dans les options');
            }
        }
        
        if (toAccountId) {
            toSelect.value = toAccountId;
            console.log('✅ Compte destination:', toAccountId, '-> select:', toSelect.value);
            if (toSelect.value !== toAccountId) {
                console.warn('⚠️ Compte destination non trouvé dans les options');
            }
        }
        
        console.log('🎯 Présélection terminée. Vérifiez le formulaire.');
        // Afficher le montant sans signe pour les virements
        const displayAmount = (transaction.type === 'virement_debit' || transaction.type === 'virement_credit') 
            ? Math.abs(parseFloat(transaction.amount) || 0)
            : parseFloat(transaction.amount) || 0;
        document.getElementById('transfer-amount').value = displayAmount;
        // Extraire la description sans le suffixe '(vers ...)' ou '(de ...)'
        let cleanDescription = transaction.description || 'Virement de fonds';
        cleanDescription = cleanDescription.replace(/\s*\(vers.*\)\s*$/i, '').replace(/\s*\(de.*\)\s*$/i, '');
        document.getElementById('transfer-description').value = cleanDescription;
        
        // Onglet 2: Infos bancaires
        document.getElementById('transfer-date').value = transaction.date || '';
        document.getElementById('transfer-reference').value = transaction.reference || '';
        document.getElementById('transfer-payment-method').value = transaction.payment_method || '';
        document.getElementById('transfer-value-date').value = transaction.value_date || '';
        document.getElementById('transfer-execution-date').value = transaction.effective_date || '';
        document.getElementById('transfer-status').value = transaction.bank_status || 'pending';
        document.getElementById('transfer-bank-notes').value = transaction.bank_notes || '';
        
        // Onglet 3: Documents
        transferFiles = [];
        
        // Charger les documents de la transaction courante
        let documentsToLoad = transaction.documents || [];
        
        // Si cette transaction n'a pas de documents et c'est un virement_credit,
        // vérifier si la transaction débit liée a des documents
        if (documentsToLoad.length === 0 && transaction.type === 'virement_credit') {
            const debitTransaction = linkedTransactions.find(t => t.type === 'virement_debit');
            if (debitTransaction && debitTransaction.documents && debitTransaction.documents.length > 0) {
                console.log('📄 Chargement des documents depuis la transaction débit liée:', debitTransaction.documents);
                documentsToLoad = debitTransaction.documents;
            }
        }
        
        if (documentsToLoad.length > 0) {
            console.log('📄 Documents existants pour ce virement:', documentsToLoad);
            renderExistingTransferDocuments(documentsToLoad);
        } else {
            renderTransferFilesList();
        }
        
        // Onglet 4: Gestion & Actions
        document.getElementById('transfer-general-comments').value = transaction.general_comments || '';
        
        // Changer le titre du modal
        const modalTitle = document.querySelector('#transferModal h3');
        if (modalTitle) {
            modalTitle.textContent = 'Modifier le Virement';
        }
        
        // Initialiser les onglets
        switchTransferTab('main-info');
        initTransferModal();
        
        // Ouvrir le modal
        document.getElementById('transferModal').style.display = 'block';
        
        showNotification('Virement chargé pour modification', 'success');
        
    } catch (error) {
        console.error('❌ Erreur lors du chargement du virement:', error);
        showNotification(error.message, 'error');
    }
}

// Fonction pour gérer les onglets du modal de virement
function switchTransferTab(tabName) {
    console.log(`🔄 Basculement vers l'onglet de virement: ${tabName}`);
    
    // Masquer tous les contenus d'onglets
    document.querySelectorAll('.transfer-tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Désactiver tous les onglets
    document.querySelectorAll('.transfer-tab').forEach(tab => {
        tab.classList.remove('border-purple-500', 'text-purple-600');
        tab.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Activer l'onglet sélectionné
    const activeTab = document.getElementById(`transfer-tab-${tabName}`);
    if (activeTab) {
        activeTab.classList.remove('border-transparent', 'text-gray-500');
        activeTab.classList.add('border-purple-500', 'text-purple-600');
    }
    
    // Afficher le contenu correspondant
    const activeContent = document.getElementById(`transfer-content-${tabName}`);
    if (activeContent) {
        activeContent.classList.remove('hidden');
    }
}

// Initialiser le modal de virement et les écouteurs d'événements
function initTransferModal() {
    // Écouteurs pour mettre à jour le récapitulatif en temps réel
    const fields = [
        'transfer-from-account',
        'transfer-to-account', 
        'transfer-amount',
        'transfer-date',
        'transfer-reference',
        'transfer-status'
    ];
    
    fields.forEach(fieldId => {
        const element = document.getElementById(fieldId);
        if (element) {
            element.addEventListener('change', updateTransferSummary);
            element.addEventListener('input', updateTransferSummary);
        }
    });
    
    // Mise à jour initiale
    updateTransferSummary();
}

// Mettre à jour le récapitulatif du virement
function updateTransferSummary() {
    // Compte source
    const fromAccountSelect = document.getElementById('transfer-from-account');
    const fromText = fromAccountSelect.options[fromAccountSelect.selectedIndex]?.text || '-';
    document.getElementById('transfer-summary-from').textContent = fromText;
    
    // Compte destination
    const toAccountSelect = document.getElementById('transfer-to-account');
    const toText = toAccountSelect.options[toAccountSelect.selectedIndex]?.text || '-';
    document.getElementById('transfer-summary-to').textContent = toText;
    
    // Montant
    const amount = parseFloat(document.getElementById('transfer-amount').value) || 0;
    document.getElementById('transfer-summary-amount').textContent = formatCurrency(amount);
    
    // Date
    const date = document.getElementById('transfer-date').value;
    document.getElementById('transfer-summary-date').textContent = date ? formatDate(date) : '-';
    
    // Référence
    const reference = document.getElementById('transfer-reference').value || '-';
    document.getElementById('transfer-summary-reference').textContent = reference;
    
    // Statut
    const statusSelect = document.getElementById('transfer-status');
    const statusText = statusSelect.options[statusSelect.selectedIndex]?.text || '⏳ En attente';
    document.getElementById('transfer-summary-status').textContent = statusText;
}

// Gestion des fichiers pour le virement
function handleTransferFiles(event) {
    const files = Array.from(event.target.files);
    transferFiles = transferFiles.concat(files);
    renderTransferFilesList();
    
    console.log(`📄 ${files.length} fichier(s) ajouté(s) au virement`);
}

// Afficher la liste des fichiers du virement
function renderTransferFilesList() {
    const container = document.getElementById('transfer-files-list');
    
    if (!transferFiles || transferFiles.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-sm italic">📁 Aucun fichier ajouté</p>';
        return;
    }
    
    container.innerHTML = transferFiles.map((file, index) => `
        <div class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
            <div class="flex items-center space-x-3">
                <i class="fas fa-file-alt text-blue-500 text-xl"></i>
                <div>
                    <p class="font-medium text-gray-900">${sanitizeHTML(file.name)}</p>
                    <p class="text-xs text-gray-500">${(file.size / 1024).toFixed(2)} KB</p>
                </div>
            </div>
            <button type="button" onclick="removeTransferFile(${index})" class="text-red-600 hover:text-red-800">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `).join('');
}

// Supprimer un fichier de la liste
function removeTransferFile(index) {
    transferFiles.splice(index, 1);
    renderTransferFilesList();
    console.log(`❌ Fichier supprimé à l'index ${index}`);
}

// Afficher les documents existants pour un virement
function renderExistingTransferDocuments(documents) {
    const documentsList = document.getElementById('transfer-files-list');
    
    if (!documentsList) return;
    
    // Si aucun document, afficher le message par défaut
    if (!documents || documents.length === 0) {
        documentsList.innerHTML = '<p class="text-gray-500 text-sm italic">📁 Aucun fichier ajouté</p>';
        return;
    }
    
    // Vider la liste avant de la re-rendre
    documentsList.innerHTML = '';
    
    // Afficher chaque document existant
    documents.forEach((doc) => {
        // Utiliser original_name si disponible, sinon file_name
        const fileName = doc.original_name || doc.file_name;
        const fileExtension = fileName.split('.').pop().toUpperCase();
        const fileSize = doc.file_size ? (doc.file_size / 1024 / 1024).toFixed(2) : '?';
        
        // Icône selon le type de fichier
        let fileIcon = 'fa-file';
        if (['PDF'].includes(fileExtension)) fileIcon = 'fa-file-pdf';
        else if (['JPG', 'JPEG', 'PNG', 'GIF'].includes(fileExtension)) fileIcon = 'fa-file-image';
        else if (['DOC', 'DOCX'].includes(fileExtension)) fileIcon = 'fa-file-word';
        else if (['XLS', 'XLSX'].includes(fileExtension)) fileIcon = 'fa-file-excel';
        
        const fileItem = document.createElement('div');
        fileItem.className = 'flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg hover:border-purple-400 transition-all';
        fileItem.innerHTML = `
            <div class="flex items-center space-x-3">
                <i class="fas ${fileIcon} text-2xl text-green-600"></i>
                <div>
                    <p class="font-medium text-gray-800">${sanitizeHTML(fileName)}</p>
                    <p class="text-xs text-gray-500">${sanitizeHTML(fileExtension)} • ${fileSize} Mo</p>
                </div>
            </div>
            <div class="flex space-x-2">
                <button type="button" onclick="previewDocument('${doc.id}', '${sanitizeHTML(fileName)}')" class="px-3 py-1 text-blue-600 hover:bg-blue-50 rounded" title="Visualiser">
                    <i class="fas fa-eye"></i>
                </button>
                <button type="button" onclick="downloadDocument('${doc.id}', '${sanitizeHTML(fileName)}')" class="px-3 py-1 text-green-600 hover:bg-green-50 rounded" title="Télécharger">
                    <i class="fas fa-download"></i>
                </button>
                <button type="button" onclick="deleteExistingTransferDocument('${doc.id}')" class="px-3 py-1 text-red-600 hover:bg-red-50 rounded" title="Supprimer">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        documentsList.appendChild(fileItem);
    });
}

// Supprimer un document existant pour un virement
async function deleteExistingTransferDocument(documentId) {
    if (!confirm('Voulez-vous vraiment supprimer définitivement ce document ?')) {
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE}/delete_document.php?id=${documentId}`, {
            method: 'DELETE'
        });
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || 'Erreur lors de la suppression');
        }
        
        showNotification('Document supprimé avec succès', 'success');
        
        // Recharger le virement pour mettre à jour la liste des documents
        if (editingId) {
            await editTransfer(editingId);
        }
    } catch (error) {
        console.error('Erreur lors de la suppression du document:', error);
        showNotification(error.message, 'error');
    }
}

// Actions du virement (placeholders pour futures fonctionnalités)
function duplicateTransfer() {
    alert('📋 Fonctionnalité de duplication en cours de développement');
}

function scheduleTransfer() {
    alert('⏰ Fonctionnalité de programmation en cours de développement');
}

function cancelTransfer() {
    if (confirm('⚠️ Êtes-vous sûr de vouloir annuler ce virement ?')) {
        closeTransferModal();
    }
}

async function saveTransfer() {
    try {
        const formData = {
            from_account_id: document.getElementById('transfer-from-account').value,
            to_account_id: document.getElementById('transfer-to-account').value,
            amount: parseFloat(document.getElementById('transfer-amount').value),
            description: document.getElementById('transfer-description').value || 'Virement de fonds',
            date: document.getElementById('transfer-date').value || new Date().toISOString().split('T')[0],
            reference: document.getElementById('transfer-reference').value || null,
            payment_method: document.getElementById('transfer-payment-method').value || null,
            value_date: document.getElementById('transfer-value-date').value || null,
            execution_date: document.getElementById('transfer-execution-date').value || null,
            status: document.getElementById('transfer-status').value || 'pending',
            bank_notes: document.getElementById('transfer-bank-notes').value || null,
            general_comments: document.getElementById('transfer-general-comments').value || null
        };
        
        if (!formData.from_account_id || !formData.to_account_id || !formData.amount) {
            showNotification('Veuillez remplir tous les champs obligatoires', 'error');
            return;
        }
        
        if (formData.from_account_id === formData.to_account_id) {
            showNotification('Les comptes source et destination doivent être différents', 'error');
            return;
        }
        
        if (formData.amount <= 0) {
            showNotification('Le montant doit être positif', 'error');
            return;
        }
        
        // ✨ DÉTECTION: Mode modification ou création
        const method = editingId ? 'PUT' : 'POST';
        const url = editingId ? `/transactions.php?id=${editingId}` : '/transactions.php?action=transfer';
        
        console.log(`📤 ${editingId ? 'Mise à jour' : 'Création'} du virement...`);
        
        // 🔧 CORRECTION: Adapter les données selon le mode
        let apiData = formData;
        if (editingId) {
            // 🎯 MODE MODIFICATION: Convertir vers format transaction standard
            // On doit envoyer account_id et type selon le type de transaction
            const transactionType = currentTransaction?.type || 'virement_debit'; // Fallback
            const fromAccount = document.getElementById('transfer-from-account').value;
            const toAccount = document.getElementById('transfer-to-account').value;
            
            // Déterminer le compte et le type selon le contexte
            if (transactionType === 'virement_debit') {
                // Débit: on modifie la transaction qui débite le compte source
                apiData = {
                    account_id: fromAccount,
                    type: 'virement_debit',
                    description: formData.description,
                    amount: formData.amount,
                    date: formData.date,
                    reference: formData.reference,
                    payment_method: formData.payment_method,
                    value_date: formData.value_date,
                    effective_date: formData.execution_date,
                    bank_notes: formData.bank_notes,
                    general_comments: formData.general_comments
                };
            } else if (transactionType === 'virement_credit') {
                // Crédit: on modifie la transaction qui crédite le compte destination
                apiData = {
                    account_id: toAccount,
                    type: 'virement_credit',
                    description: formData.description,
                    amount: formData.amount,
                    date: formData.date,
                    reference: formData.reference,
                    payment_method: formData.payment_method,
                    value_date: formData.value_date,
                    effective_date: formData.execution_date,
                    bank_notes: formData.bank_notes,
                    general_comments: formData.general_comments
                };
            }
            
            console.log('🔧 Données transformées pour l\'API (mode modification):');
            console.log('  account_id:', apiData.account_id);
            console.log('  type:', apiData.type);
            console.log('  description:', apiData.description);
            console.log('  amount:', apiData.amount);
        } else {
            // 📝 MODE CRÉATION: Utiliser les champs virement standards
            apiData = formData;
        }
        
        console.log('📤 Données finales envoyées:', apiData);
        
        const response = await apiCall(url, {
            method: method,
            body: apiData
        });
        
        // Si des fichiers sont attachés, les uploader
        if (transferFiles.length > 0) {
            console.log(`📄 Upload de ${transferFiles.length} fichier(s) pour le virement...`);
            
            if (editingId) {
                // En mode modification, on a déjà l'ID de la transaction
                await uploadTransferDocuments(editingId);
            } else if (response.data && response.data.transfer_id) {
                // En mode création, utiliser le debit_transaction_id
                await uploadTransferDocuments(response.data.debit_transaction_id);
            }
        }
        
        const message = editingId ? 'Virement mis à jour avec succès' : 'Virement effectué avec succès';
        showNotification(message, 'success');
        
        await loadAllData();
        updateAllDisplays();
        
        if (currentSection === 'transactions') {
            updateTransactionsDisplay();
        } else if (currentSection === 'dashboard') {
            updateDashboard();
        }
        
        closeTransferModal();
        
        // Réinitialiser editingId après la sauvegarde
        editingId = null;
        
    } catch (error) {
        console.error('Erreur lors du virement:', error);
        showNotification(error.message, 'error');
    }
}

// Fonction pour uploader les documents du virement
async function uploadTransferDocuments(transactionId) {
    try {
        for (const file of transferFiles) {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('transaction_id', transactionId);

            const response = await fetch(API_BASE + '/upload_document.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                const errorMsg = errorData.message || errorData.error || `Erreur HTTP ${response.status}`;
                throw new Error(`Erreur lors de l'upload de ${file.name}: ${errorMsg}`);
            }

            console.log(`✅ Document ${file.name} uploadé avec succès`);
        }
        
        console.log('✅ Tous les documents du virement ont été uploadés');
        
    } catch (error) {
        console.error('❌ Erreur lors de l\'upload des documents:', error);
        showNotification(`Erreur lors de l'upload des documents: ${error.message}`, 'warning');
        // Ne pas bloquer le virement si l'upload échoue
    }
}

// Gestion des tiers
function showTiersTab(type) {
    console.log(`🔄 Changement d'onglet vers: ${type}`);
    
    // Cacher tous les onglets
    document.querySelectorAll('.tiers-tab').forEach(tab => tab.style.display = 'none');
    
    // Afficher l'onglet sélectionné
    const contentElement = document.getElementById(type + '-content');
    if (contentElement) {
        contentElement.style.display = 'block';
        console.log(`✅ Onglet ${type}-content affiché`);
    } else {
        console.error(`❌ Élément ${type}-content non trouvé`);
    }
    
    // Mettre à jour les styles des onglets
    document.querySelectorAll('[id$="-tab"]').forEach(tab => {
        tab.classList.remove('border-blue-500', 'text-blue-600');
        tab.classList.add('text-gray-500');
    });
    
    const activeTab = document.getElementById(type + '-tab');
    if (activeTab) {
        activeTab.classList.add('border-blue-500', 'text-blue-600');
        activeTab.classList.remove('text-gray-500');
        console.log(`✅ Onglet ${type}-tab activé`);
    } else {
        console.error(`❌ Élément ${type}-tab non trouvé`);
    }
    
    // Déterminer le type de tiers
    const oldType = currentTiersType;
    currentTiersType = type === 'clients' ? 'client' : 'fournisseur';
    console.log(`🔄 Type de tiers changé de "${oldType}" vers "${currentTiersType}"`);
    
    // Mettre à jour l'affichage
    updateTiersDisplay();
}

// === NOUVELLES FONCTIONS POUR LES SECTIONS SÉPARÉES ===

async function updateClientsDisplay() {
    try {
        console.log('🔄 Mise à jour de l\'affichage des clients');
        
        // Charger les données clients
        currentTiersType = 'client';
        initializeClientsFilters();
        applyClientsFilters();
        
    } catch (error) {
        console.error('❌ Erreur lors de la mise à jour des clients:', error);
        showNotification('Erreur lors du chargement des clients: ' + error.message, 'error');
    }
}

async function updateFournisseursDisplay() {
    try {
        console.log('🔄 Mise à jour de l\'affichage des fournisseurs');
        
        // Charger les données fournisseurs
        currentTiersType = 'fournisseur';
        initializeFournisseursFilters();
        applyFournisseursFilters();
        
    } catch (error) {
        console.error('❌ Erreur lors de la mise à jour des fournisseurs:', error);
        showNotification('Erreur lors du chargement des fournisseurs: ' + error.message, 'error');
    }
}

async function updateTiersDisplay() {
    try {
        console.log(`🔄 Mise à jour de l'affichage des tiers - Type actuel: ${currentTiersType}`);
        
        // Utiliser la nouvelle fonction avec filtres
        updateTiersDisplayWithFilters();
        
    } catch (error) {
        console.error('❌ Erreur lors de la mise à jour des tiers:', error);
        showNotification('Erreur lors du chargement des tiers: ' + error.message, 'error');
    }
}

function updateTiersTable(tableId, data) {
    const tbody = document.getElementById(tableId);
    
    if (!tbody) {
        console.error(`❌ Table avec ID "${tableId}" non trouvée dans le DOM`);
        return;
    }
    
    console.log(`📊 Mise à jour de la table ${tableId} avec ${data ? data.length : 0} éléments`);
    
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-gray-500">Aucun tiers trouvé</td></tr>';
        return;
    }
    
    // Vérifier que chaque tiers a un ID valide
    data.forEach((tiers, index) => {
        if (!tiers.id) {
            console.error(`❌ Tiers à l'index ${index} n'a pas d'ID:`, tiers);
        } else {
            console.log(`✅ Tiers ${index}: ID="${tiers.id}", Nom="${tiers.raison_sociale}"`);
        }
    });
    
    tbody.innerHTML = data.map(tiers => {
        // Sécuriser l'ID pour éviter les problèmes d'injection
        const safeId = sanitizeHTML(tiers.id || '');
        
        if (!safeId) {
            console.error('❌ ID manquant pour le tiers:', tiers);
            return '';
        }
        
        return `
        <tr>
            <td class="px-4 py-3">${sanitizeHTML(tiers.code || '-')}</td>
            <td class="px-4 py-3">${sanitizeHTML(tiers.raison_sociale)}</td>
            <td class="px-4 py-3">${sanitizeHTML(tiers.contact || '-')}</td>
            <td class="px-4 py-3">${sanitizeHTML(tiers.telephone || '-')}</td>
            <td class="px-4 py-3">${sanitizeHTML(tiers.email || '-')}</td>
            <td class="px-4 py-3 text-right">${sanitizeHTML(formatCurrency(tiers.solde || 0))}</td>
            <td class="px-4 py-3 text-center no-print">
                <button onclick="editTiers('${safeId}')" class="text-blue-600 hover:text-blue-800 mr-2" title="Modifier ${sanitizeHTML(tiers.raison_sociale)}">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="deleteTiers('${safeId}')" class="text-red-600 hover:text-red-800" title="Supprimer ${sanitizeHTML(tiers.raison_sociale)}">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
    }).filter(row => row !== '').join('');
    
    console.log(`✅ Table ${tableId} mise à jour avec succès`);
}

// Fonction pour gérer les onglets des Tiers
function switchTiersTab(tabName) {
    console.log(`🔄 Basculement vers l'onglet: ${tabName}`);
    
    // Masquer tous les contenus d'onglets
    const allContents = document.querySelectorAll('.tiers-tab-content');
    console.log(`📋 Nombre de contenus d'onglets trouvés: ${allContents.length}`);
    allContents.forEach(content => {
        content.classList.add('hidden');
    });
    
    // Désactiver tous les onglets
    const allTabs = document.querySelectorAll('.tiers-tab');
    console.log(`📋 Nombre d'onglets trouvés: ${allTabs.length}`);
    allTabs.forEach(tab => {
        tab.classList.remove('active');
        tab.classList.remove('border-blue-500', 'text-blue-600');
        tab.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Activer l'onglet sélectionné
    const activeTab = document.getElementById(`tab-${tabName}`);
    if (activeTab) {
        activeTab.classList.add('active');
        activeTab.classList.remove('border-transparent', 'text-gray-500');
        activeTab.classList.add('border-blue-500', 'text-blue-600');
        console.log(`✅ Onglet ${tabName} activé`);
    } else {
        console.error(`❌ Onglet ${tabName} introuvable`);
    }
    
    // Afficher le contenu correspondant
    const activeContent = document.getElementById(`content-${tabName}`);
    if (activeContent) {
        activeContent.classList.remove('hidden');
        console.log(`✅ Contenu ${tabName} affiché`);
        
        // Vérifier que le contenu est bien visible
        const computedStyle = window.getComputedStyle(activeContent);
        console.log(`📐 Display du contenu ${tabName}: ${computedStyle.display}`);
    } else {
        console.error(`❌ Contenu ${tabName} introuvable`);
    }
}

/**
 * Fonction pour forcer l'affichage des onglets
 * Corrige les problèmes de visibilité CSS
 */
function forceShowTiersTabs() {
    console.log('🔧 Forçage de l\'affichage des onglets...');
    
    // S'assurer que la navigation des onglets est visible
    const tabNavigation = document.querySelector('.border-b.border-gray-200');
    if (tabNavigation) {
        tabNavigation.style.display = 'block';
        tabNavigation.style.visibility = 'visible';
        console.log('✅ Navigation des onglets forcée visible');
    } else {
        console.error('❌ Navigation des onglets introuvable');
    }
    
    // S'assurer que tous les onglets sont visibles
    const allTabs = document.querySelectorAll('.tiers-tab');
    allTabs.forEach((tab, index) => {
        if (tab) {
            tab.style.display = 'inline-block';
            tab.style.visibility = 'visible';
            console.log(`✅ Onglet ${index + 1} forcé visible`);
        }
    });
    
    console.log(`✅ ${allTabs.length} onglets forcés visibles`);
}

function openTiersModal(type) {
    console.log(`🚀 === OUVERTURE MODAL TIERS CORRIGÉE POUR TYPE: ${type.toUpperCase()} ===`);
    
    try {
        // Vérifier que les éléments DOM existent
        const modal = document.getElementById('tiersModal');
        const modalTitle = document.getElementById('tiersModalTitle');
        const form = document.getElementById('tiersForm');
        
        if (!modal) {
            console.error('❌ Élément tiersModal introuvable');
            return false;
        }
        
        if (!modalTitle) {
            console.error('❌ Élément tiersModalTitle introuvable');
            return false;
        }
        
        if (!form) {
            console.error('❌ Élément tiersForm introuvable');
            return false;
        }
        
        // Réinitialiser les variables globales
        currentTiersType = type;
        editingId = null;
        
        console.log(`✅ Type défini: ${currentTiersType}`);
        
        // Définir les titres
        const titles = {
            client: 'Nouveau Client',
            fournisseur: 'Nouveau Fournisseur'
        };
        
        // Valider le type
        if (!titles[type]) {
            console.error(`❌ Type de tiers non valide: ${type}`);
            return false;
        }
        
        // Fermer la modal si elle est déjà ouverte
        modal.style.display = 'none';
        console.log('🔄 Modal temporairement fermée pour réinitialisation');
        
        // Appliquer le titre
        modalTitle.textContent = titles[type];
        console.log(`✅ Titre défini: ${titles[type]}`);
        
        // Réinitialiser le formulaire
        form.reset();
        console.log('✅ Formulaire réinitialisé');
        
        // ÉTAPE CRITIQUE: Forcer l'affichage des onglets AVANT d'afficher la modal
        forceShowTiersTabs();
        
        // NOUVELLE APPROCHE: Initialisation immédiate des onglets
        setTimeout(() => {
            console.log('🔧 Initialisation des onglets (phase 1)...');
            
            // Forcer à nouveau l'affichage des onglets
            forceShowTiersTabs();
            
            // Initialiser l'onglet identité
            switchTiersTab('identite');
            console.log('✅ Onglet identité initialisé');
            
            // Vérifier que les onglets sont bien visibles
            const tabButtons = document.querySelectorAll('.tiers-tab');
            console.log(`✅ Nombre d'onglets trouvés: ${tabButtons.length}`);
            
            // Vérifier que les contenus d'onglets existent
            const tabContents = document.querySelectorAll('.tiers-tab-content');
            console.log(`✅ Nombre de contenus d'onglets trouvés: ${tabContents.length}`);
            
            // S'assurer que l'onglet identité est visible
            const identiteContent = document.getElementById('content-identite');
            if (identiteContent) {
                identiteContent.classList.remove('hidden');
                console.log('✅ Contenu identité rendu visible');
            }
            
            // Étape 2: Afficher la modal après initialisation
            setTimeout(() => {
                modal.style.display = 'block';
                console.log('✅ Modal affichée');
                
                // Vérifier que la modal est bien visible
                const computedStyle = window.getComputedStyle(modal);
                console.log(`✅ Display calculé: ${computedStyle.display}`);
                console.log(`✅ Visibility calculée: ${computedStyle.visibility}`);
                console.log(`✅ Z-index calculé: ${computedStyle.zIndex}`);
                
                // Étape 3: Vérification finale après affichage
                setTimeout(() => {
                    console.log('🔍 === VÉRIFICATION FINALE ===');
                    
                    // Vérifier que la navigation des onglets est visible
                    const navigation = document.querySelector('.border-b.border-gray-200');
                    if (navigation) {
                        const navStyle = window.getComputedStyle(navigation);
                        console.log(`📊 Navigation - Display: ${navStyle.display}, Visibility: ${navStyle.visibility}`);
                    }
                    
                    // Si les onglets ne sont toujours pas visibles, les forcer à nouveau
                    const tabsVisible = document.querySelectorAll('.tiers-tab:not([style*="display: none"])').length > 0;
                    if (!tabsVisible) {
                        console.warn('⚠️ Onglets toujours invisibles, correction d\'urgence...');
                        forceShowTiersTabs();
                        switchTiersTab('identite');
                    }
                    
                    console.log('🎯 Ouverture de modal terminée');
                }, 100);
                
            }, 50);
            
        }, 10);
        
        // Initialiser les dates pour un nouveau tiers
        if (!editingId) {
            const today = new Date().toISOString().split('T')[0];
            const dateCreationEl = document.getElementById('tiers-date-creation');
            const dateModificationEl = document.getElementById('tiers-date-modification');
            
            if (dateCreationEl) {
                dateCreationEl.value = today;
                console.log('✅ Date de création initialisée');
            }
            
            if (dateModificationEl) {
                dateModificationEl.value = today;
                console.log('✅ Date de modification initialisée');
            }
        }
        
        return true;
        
    } catch (error) {
        console.error('❌ Erreur lors de l\'ouverture de la modal:', error);
        return false;
    }
}

function closeTiersModal() {
    console.log('🔄 Fermeture modal tiers');
    
    const modal = document.getElementById('tiersModal');
    if (!modal) {
        console.error('❌ Élément tiersModal introuvable lors de la fermeture');
        return;
    }
    
    modal.style.display = 'none';
    console.log('✅ Modal fermée');
    
    // Réinitialiser les variables
    currentTiersType = 'client';
    editingId = null;
    
    // Optionnel: réinitialiser le formulaire
    const form = document.getElementById('tiersForm');
    if (form) {
        form.reset();
        console.log('✅ Formulaire réinitialisé lors de la fermeture');
    }
}

// Fonction utilitaire pour récupérer la valeur d'un champ de manière sécurisée
function getFieldValue(fieldId, defaultValue = '') {
    const element = document.getElementById(fieldId);
    if (element) {
        return element.value;
    } else {
        console.warn(`⚠️ Champ non trouvé: ${fieldId}, utilisation valeur par défaut: "${defaultValue}"`);
        return defaultValue;
    }
}

async function saveTiers() {
    try {
        console.log('💾 Début sauvegarde Tiers...');
        
        // Vérification des champs essentiels d'abord
        const raisonSocialeElement = document.getElementById('tiers-raison-sociale');
        if (!raisonSocialeElement) {
            throw new Error('Élément raison sociale introuvable dans le DOM');
        }
        
        // Récupérer les données de base (compatibilité avec ancien système)
        const baseFormData = {
            type: currentTiersType,
            code: getFieldValue('tiers-code'),
            raison_sociale: getFieldValue('tiers-raison-sociale'),
            contact: getFieldValue('tiers-contact'),
            telephone: getFieldValue('tiers-telephone'),
            email: getFieldValue('tiers-email'),
            siret: getFieldValue('tiers-siret'),
            adresse: getFieldValue('tiers-adresse'),
            notes: getFieldValue('tiers-notes')
        };
        
        // Ajouter les données étendues si les champs existent
        const extendedFormData = {
            // Onglet Identité
            reference: getFieldValue('tiers-reference'),
            type_client: getFieldValue('tiers-type'),
            famille: getFieldValue('tiers-famille'),
            statut: getFieldValue('tiers-statut', 'actif'),
            note_interne: getFieldValue('tiers-note-interne'),
            
            // Onglet Infos générales
            code_postal: getFieldValue('tiers-code-postal'),
            ville: getFieldValue('tiers-ville'),
            wilaya: getFieldValue('tiers-wilaya'),
            adresse_livraison: getFieldValue('tiers-adresse-livraison'),
            
            // Onglet Contact
            telephone_fixe: getFieldValue('tiers-telephone-fixe'),
            mobile: getFieldValue('tiers-mobile'),
            fax: getFieldValue('tiers-fax'),
            site_web: getFieldValue('tiers-site-web'),
            
            // Onglet Légal
            identifiant_fiscal: getFieldValue('tiers-identifiant-fiscal'),
            nis: getFieldValue('tiers-nis'),
            article_imposition: getFieldValue('tiers-article-imposition'),
            
            // Onglet Comptabilité
            code_comptable: getFieldValue('tiers-code-comptable'),
            numero_compte: getFieldValue('tiers-numero-compte'),
            rib: getFieldValue('tiers-rib'),
            solde_max: getFieldValue('tiers-solde-max'),
            exoneration_tva: getFieldValue('tiers-exoneration-tva', 'non'),
            mode_paiement: getFieldValue('tiers-mode-paiement'),
            conditions_echeance: getFieldValue('tiers-conditions-echeance'),
            
            // Onglet Dates
            date_creation: getFieldValue('tiers-date-creation'),
            date_modification: getFieldValue('tiers-date-modification'),
            date1: getFieldValue('tiers-date1'),
            date2: getFieldValue('tiers-date2'),
            date3: getFieldValue('tiers-date3'),
            
            // Onglet Autres
            mots_cles: getFieldValue('tiers-mots-cles'),
            solvabilite: getFieldValue('tiers-solvabilite')
        };
        
        // Fusionner les données
        const formData = { ...baseFormData, ...extendedFormData };
        
        // Validation
        if (!formData.raison_sociale) {
            showNotification('La raison sociale est obligatoire', 'error');
            return;
        }
        
        // Mettre à jour la date de modification
        if (editingId) {
            formData.date_modification = new Date().toISOString().split('T')[0];
            const dateModifElement = document.getElementById('tiers-date-modification');
            if (dateModifElement) {
                dateModifElement.value = formData.date_modification;
            }
        }
        
        console.log('🔄 Données à envoyer:', formData);
        
        const method = editingId ? 'PUT' : 'POST';
        const url = editingId ? `/tiers.php?id=${editingId}` : '/tiers.php';
        
        await apiCall(url, {
            method: method,
            body: formData
        });
        
        const message = editingId ? 'Tiers mis à jour avec succès' : 'Tiers créé avec succès';
        showNotification(message, 'success');
        
        await loadAllData();
        updateTiersSelects();
        updateTiersDisplay();
        
        closeTiersModal();
        
    } catch (error) {
        console.error('❌ Erreur lors de l\'enregistrement:', error);
        console.error('Stack trace:', error.stack);
        showNotification(`Erreur: ${error.message}`, 'error');
    }
}

async function editTiers(id) {
    try {
        console.log('🔄 Début de editTiers avec ID:', id);
        
        // Vérification de l'ID
        if (!id || id === '') {
            throw new Error('ID du tiers manquant');
        }
        
        // Vérifier que la modal existe
        const modal = document.getElementById('tiersModal');
        if (!modal) {
            throw new Error('Modal des tiers non trouvée dans le DOM');
        }
        
        console.log('📡 Appel API en cours...');
        const response = await apiCall(`/tiers.php?id=${id}`);
        console.log('📡 Réponse API reçue:', response);
        
        // L'API retourne tous les tiers même avec un ID spécifique
        // Il faut trouver le bon tiers dans le tableau
        let tiers = response.data;
        
        if (Array.isArray(tiers)) {
            console.log('🔍 L\'API a retourné un tableau, recherche du tiers avec ID:', id);
            tiers = tiers.find(t => t.id === id);
            if (!tiers) {
                throw new Error(`Tiers avec ID "${id}" non trouvé dans la réponse API`);
            }
            console.log('✅ Tiers trouvé:', tiers);
        }
        
        if (!tiers) {
            throw new Error('Tiers non trouvé - données vides');
        }
        
        console.log('📋 Données du tiers:', tiers);
        
        editingId = id;
        currentTiersType = tiers.type;
        
        console.log('🔧 Remplissage du formulaire...');
        
        // Définir tous les champs avec leurs valeurs
        const fields = [
            // Onglet Identité
            { id: 'tiers-code', value: tiers.code || '', label: 'Code' },
            { id: 'tiers-reference', value: tiers.reference || '', label: 'Référence' },
            { id: 'tiers-raison-sociale', value: tiers.raison_sociale || '', label: 'Raison sociale' },
            { id: 'tiers-type', value: tiers.type_client || '', label: 'Type de client' },
            { id: 'tiers-famille', value: tiers.famille || '', label: 'Famille' },
            { id: 'tiers-statut', value: tiers.statut || 'actif', label: 'Statut' },
            { id: 'tiers-note-interne', value: tiers.note_interne || '', label: 'Note interne' },
            
            // Onglet Adresse
            { id: 'tiers-adresse', value: tiers.adresse || '', label: 'Adresse' },
            { id: 'tiers-code-postal', value: tiers.code_postal || '', label: 'Code postal' },
            { id: 'tiers-ville', value: tiers.ville || '', label: 'Ville' },
            { id: 'tiers-wilaya', value: tiers.wilaya || '', label: 'Wilaya' },
            { id: 'tiers-adresse-livraison', value: tiers.adresse_livraison || '', label: 'Adresse livraison' },
            
            // Onglet Contact
            { id: 'tiers-contact', value: tiers.contact || '', label: 'Contact' },
            { id: 'tiers-telephone', value: tiers.telephone || '', label: 'Téléphone' },
            { id: 'tiers-mobile', value: tiers.mobile || '', label: 'Mobile' },
            { id: 'tiers-fax', value: tiers.fax || '', label: 'Fax' },
            { id: 'tiers-email', value: tiers.email || '', label: 'Email' },
            { id: 'tiers-site-web', value: tiers.site_web || '', label: 'Site web' },
            
            // Onglet Légal
            { id: 'tiers-identifiant-fiscal', value: tiers.identifiant_fiscal || '', label: 'Identifiant fiscal' },
            { id: 'tiers-nis', value: tiers.nis || '', label: 'NIS' },
            { id: 'tiers-siret', value: tiers.siret || '', label: 'SIRET/RC' },
            { id: 'tiers-article-imposition', value: tiers.article_imposition || '', label: 'Article imposition' },
            
            // Onglet Comptabilité
            { id: 'tiers-code-comptable', value: tiers.code_comptable || '', label: 'Code comptable' },
            { id: 'tiers-numero-compte', value: tiers.numero_compte || '', label: 'Numéro compte' },
            { id: 'tiers-rib', value: tiers.rib || '', label: 'RIB/IBAN' },
            { id: 'tiers-solde-actuel', value: tiers.solde || '0.00', label: 'Solde actuel' },
            { id: 'tiers-solde-max', value: tiers.solde_max || '', label: 'Solde maximum' },
            { id: 'tiers-exoneration-tva', value: tiers.exoneration_tva || 'non', label: 'Exonération TVA' },
            { id: 'tiers-mode-paiement', value: tiers.mode_paiement || '', label: 'Mode paiement' },
            { id: 'tiers-conditions-echeance', value: tiers.conditions_echeance || '', label: 'Conditions échéance' },
            
            // Onglet Dates
            { id: 'tiers-date-creation', value: tiers.date_creation || '', label: 'Date création' },
            { id: 'tiers-date-modification', value: tiers.date_modification || '', label: 'Date modification' },
            { id: 'tiers-date1', value: tiers.date1 || '', label: 'Date 1' },
            { id: 'tiers-date2', value: tiers.date2 || '', label: 'Date 2' },
            { id: 'tiers-date3', value: tiers.date3 || '', label: 'Date 3' },
            
            // Onglet Autres
            { id: 'tiers-notes', value: tiers.notes || '', label: 'Notes' },
            { id: 'tiers-mots-cles', value: tiers.mots_cles || '', label: 'Mots-clés' },
            { id: 'tiers-solvabilite', value: tiers.solvabilite || '', label: 'Solvabilité' }
        ];
        
        // Remplir tous les champs
        fields.forEach(field => {
            const element = document.getElementById(field.id);
            if (element) {
                element.value = field.value;
                console.log(`  ✅ ${field.label}: "${field.value}"`);
            } else {
                console.warn(`  ⚠️ Élément ${field.id} non trouvé (${field.label}) - peut être un nouveau champ`);
            }
        });
        
        // Gestion spéciale pour téléphone fixe (nouveau champ séparé)
        const telephoneFixeElement = document.getElementById('tiers-telephone-fixe');
        if (telephoneFixeElement && tiers.telephone_fixe) {
            telephoneFixeElement.value = tiers.telephone_fixe;
            console.log(`  ✅ Téléphone fixe: "${tiers.telephone_fixe}"`);
        }
        
        const titles = {
            client: 'Modifier le Client',
            fournisseur: 'Modifier le Fournisseur'
        };
        
        const titleElement = document.getElementById('tiersModalTitle');
        if (titleElement) {
            titleElement.textContent = titles[tiers.type] || 'Modifier le Tiers';
            console.log('🏷️ Titre de la modal défini:', titles[tiers.type]);
        } else {
            console.error('❌ Élément tiersModalTitle non trouvé');
        }
        
        // Basculer vers le premier onglet
        switchTiersTab('identite');
        
        // Afficher la modal
        modal.style.display = 'block';
        console.log('🎭 Modal affichée');
        
        // Notification de succès
        showNotification(`Tiers "${tiers.raison_sociale}" chargé pour édition`, 'success');
        
    } catch (error) {
        console.error('❌ Erreur lors du chargement du tiers:', error);
        console.error('Stack trace:', error.stack);
        showNotification(`Erreur lors du chargement: ${error.message}`, 'error');
    }
}

async function deleteTiers(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce tiers ?')) {
        return;
    }
    
    try {
        await apiCall(`/tiers.php?id=${id}`, {
            method: 'DELETE'
        });
        
        showNotification('Tiers supprimé avec succès', 'success');
        
        await loadAllData();
        updateTiersSelects();
        updateTiersDisplay();
        
    } catch (error) {
        console.error('Erreur lors de la suppression:', error);
        showNotification(error.message, 'error');
    }
}

// Gestion des comptes
async function updateBanqueDisplay() {
    const banques = appData.comptes.filter(c => c.type === 'banque');
    updateCompteCards('banque-cards', banques);
    updateCompteTransactions('banque-transactions', 'banque');
}

async function updateCaisseDisplay() {
    const caisses = appData.comptes.filter(c => c.type === 'caisse');
    updateCompteCards('caisse-cards', caisses);
    updateCompteTransactions('caisse-transactions', 'caisse');
    
    // Charger l'historique des caisses si l'onglet historique est actif
    const historiqueTab = document.getElementById('caisse-historique-content');
    if (historiqueTab && !historiqueTab.classList.contains('hidden')) {
        await updateCaisseHistoriqueDisplay();
    }
}

/**
 * Met à jour l'affichage de la section Achats
 */
async function updateAchatsDisplay() {
    console.log('🛒 Mise à jour affichage Achats...');
    
    // Par défaut, afficher l'onglet Vue d'ensemble
    showAchatsTab('vue-ensemble');
    
    console.log('✅ Section Achats mise à jour');
}

function updateCompteCards(containerId, comptes) {
    const container = document.getElementById(containerId);
    
    if (!comptes || comptes.length === 0) {
        container.innerHTML = '<div class="col-span-full text-center py-8 text-gray-500">Aucun compte trouvé</div>';
        return;
    }
    
    container.innerHTML = comptes.map(compte => `
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-semibold">${sanitizeHTML(compte.name)}</h4>
                <div class="flex gap-2">
                    <button onclick="editAccount('${compte.id}')" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteAccount('${compte.id}')" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="space-y-2">
                ${compte.bank ? `<p class="text-sm text-gray-600"><strong>Banque:</strong> ${sanitizeHTML(compte.bank)}</p>` : ''}
                <p class="text-sm text-gray-600"><strong>Solde:</strong> 
                    <span class="font-semibold ${parseFloat(compte.balance) >= 0 ? 'text-green-600' : 'text-red-600'}">
                        ${sanitizeHTML(formatCurrency(compte.balance))}
                    </span>
                </p>
                ${compte.description ? `<p class="text-sm text-gray-600">${sanitizeHTML(compte.description)}</p>` : ''}
            </div>
        </div>
    `).join('');
}

async function updateCompteTransactions(tableId, type) {
    try {
        const transactions = appData.transactions.filter(t => {
            const compte = appData.comptes.find(c => c.id === t.account_id);
            return compte && compte.type === type;
        });
        
        const tbody = document.getElementById(tableId);
        
        if (!transactions || transactions.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-gray-500">Aucune transaction trouvée</td></tr>';
            return;
        }
        
        tbody.innerHTML = transactions.slice(0, 20).map(transaction => `
            <tr>
                <td class="px-4 py-3">${sanitizeHTML(formatDate(transaction.date))}</td>
                <td class="px-4 py-3">${sanitizeHTML(transaction.account_name)}</td>
                <td class="px-4 py-3">${sanitizeHTML(transaction.description)}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded text-xs ${sanitizeHTML(getTypeClass(transaction.type))}">
                        ${sanitizeHTML(getTypeLabel(transaction.type))}
                    </span>
                </td>
                <td class="px-4 py-3 text-right ${sanitizeHTML(getAmountClass(transaction.type))}">
                    ${sanitizeHTML(formatCurrencyForDisplay(transaction))}
                </td>
                <td class="px-4 py-3 text-right">
                    ${sanitizeHTML(formatCurrency(transaction.balance_after || 0))}
                </td>
                <td class="px-4 py-3 text-center no-print">
                    <button onclick="viewTransactionDetails(${JSON.stringify(transaction).replace(/"/g, '&quot;')})" class="text-blue-600 hover:text-blue-800 p-1 rounded transition-colors" title="Visualiser les détails">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
        `).join('');
        
    } catch (error) {
        console.error('Erreur lors du chargement des transactions:', error);
    }
}

function openAccountModal(type) {
    currentAccountType = type;
    editingId = null;
    
    const titles = {
        caisse: 'Nouvelle Caisse',
        banque: 'Nouveau Compte Bancaire'
    };
    
    document.getElementById('accountModalTitle').textContent = titles[type];
    document.getElementById('accountModal').style.display = 'block';
    document.getElementById('accountForm').reset();
    
    const bankField = document.getElementById('bank-field');
    bankField.style.display = type === 'banque' ? 'block' : 'none';
}

function closeAccountModal() {
    document.getElementById('accountModal').style.display = 'none';
}

async function saveAccount() {
    try {
        const formData = {
            type: currentAccountType,
            name: document.getElementById('account-name').value,
            balance: parseFloat(document.getElementById('account-balance').value) || 0,
            description: document.getElementById('account-description').value
        };
        
        if (currentAccountType === 'banque') {
            formData.bank = document.getElementById('account-bank').value;
        }
        
        if (!formData.name) {
            showNotification('Le nom du compte est obligatoire', 'error');
            return;
        }
        
        const method = editingId ? 'PUT' : 'POST';
        const url = editingId ? `/comptes.php?id=${editingId}` : '/comptes.php';
        
        await apiCall(url, {
            method: method,
            body: formData
        });
        
        const message = editingId ? 'Compte mis à jour avec succès' : 'Compte créé avec succès';
        showNotification(message, 'success');
        
        await loadAllData();
        updateAccountSelects();
        
        if (currentSection === 'banque') {
            updateBanqueDisplay();
        } else if (currentSection === 'caisse') {
            updateCaisseDisplay();
        }
        
        closeAccountModal();
        
    } catch (error) {
        console.error('Erreur lors de l\'enregistrement:', error);
        showNotification(error.message, 'error');
    }
}

async function editAccount(id) {
    try {
        const response = await apiCall(`/comptes.php?id=${id}`);
        const compte = response.data;
        
        if (!compte) {
            throw new Error('Compte non trouvé');
        }
        
        editingId = id;
        currentAccountType = compte.type;
        
        document.getElementById('account-name').value = compte.name || '';
        document.getElementById('account-balance').value = compte.balance || 0;
        document.getElementById('account-description').value = compte.description || '';
        
        if (compte.type === 'banque') {
            document.getElementById('account-bank').value = compte.bank || '';
        }
        
        const titles = {
            caisse: 'Modifier la Caisse',
            banque: 'Modifier le Compte Bancaire'
        };
        
        document.getElementById('accountModalTitle').textContent = titles[compte.type];
        
        const bankField = document.getElementById('bank-field');
        bankField.style.display = compte.type === 'banque' ? 'block' : 'none';
        
        document.getElementById('accountModal').style.display = 'block';
        
    } catch (error) {
        console.error('Erreur lors du chargement du compte:', error);
        showNotification(error.message, 'error');
    }
}

async function deleteAccount(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce compte ?')) {
        return;
    }
    
    try {
        await apiCall(`/comptes.php?id=${id}`, {
            method: 'DELETE'
        });
        
        showNotification('Compte supprimé avec succès', 'success');
        
        await loadAllData();
        updateAccountSelects();
        
        if (currentSection === 'banque') {
            updateBanqueDisplay();
        } else if (currentSection === 'caisse') {
            updateCaisseDisplay();
        }
        
    } catch (error) {
        console.error('Erreur lors de la suppression:', error);
        showNotification(error.message, 'error');
    }
}

// Rapports
function updateRapportsDisplay() {
    if (appData.transactions) {
        const stats = calculateStatsFromData(appData.transactions);
        
        document.getElementById('rapport-recettes').textContent = formatCurrency(stats.total_recettes);
        document.getElementById('rapport-depenses').textContent = formatCurrency(stats.total_depenses);
        document.getElementById('rapport-solde').textContent = formatCurrency(stats.total_recettes - stats.total_depenses);
        document.getElementById('rapport-nb-transactions').textContent = stats.total_transactions;
    }
}

function calculateStatsFromData(transactions) {
    const recettes = transactions.filter(t => t.type === 'recette' || t.type === 'virement_credit');
    const depenses = transactions.filter(t => t.type === 'depense' || t.type === 'virement_debit');
    
    return {
        total_recettes: recettes.reduce((sum, t) => sum + parseFloat(t.amount || 0), 0),
        total_depenses: depenses.reduce((sum, t) => sum + parseFloat(t.amount || 0), 0),
        total_transactions: transactions.length
    };
}

function togglePeriodeFields() {
    const type = document.getElementById('rapport-type').value;
    const fields = document.getElementById('periode-fields');
    fields.style.display = type === 'periode' ? 'block' : 'none';
}

async function generateReport() {
    try {
        const type = document.getElementById('rapport-type').value;
        let filters = {};
        
        if (type === 'periode') {
            filters.date_from = document.getElementById('rapport-debut').value;
            filters.date_to = document.getElementById('rapport-fin').value;
            
            if (!filters.date_from || !filters.date_to) {
                showNotification('Veuillez sélectionner une période', 'error');
                return;
            }
        } else if (type === 'mensuel') {
            const now = new Date();
            filters.date_from = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-01`;
            filters.date_to = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-31`;
        } else if (type === 'annuel') {
            const now = new Date();
            filters.date_from = `${now.getFullYear()}-01-01`;
            filters.date_to = `${now.getFullYear()}-12-31`;
        }
        
        const transactions = await loadTransactions(filters);
        updateReportTable(transactions);
        
        document.getElementById('rapport-details').style.display = 'block';
        
        showNotification('Rapport généré avec succès', 'success');
        
    } catch (error) {
        console.error('Erreur lors de la génération du rapport:', error);
        showNotification(error.message, 'error');
    }
}

function updateReportTable(transactions) {
    const tbody = document.getElementById('rapport-table');
    
    if (!transactions || transactions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-gray-500">Aucune transaction dans cette période</td></tr>';
        return;
    }
    
    tbody.innerHTML = transactions.map(transaction => `
        <tr>
            <td class="px-4 py-3">${sanitizeHTML(formatDate(transaction.date))}</td>
            <td class="px-4 py-3">
                <span class="px-2 py-1 rounded text-xs ${sanitizeHTML(getTypeClass(transaction.type))}">
                    ${sanitizeHTML(getTypeLabel(transaction.type))}
                </span>
            </td>
            <td class="px-4 py-3">${sanitizeHTML(transaction.description)}</td>
            <td class="px-4 py-3">${sanitizeHTML(transaction.account_name || 'N/A')}</td>
            <td class="px-4 py-3 text-right ${sanitizeHTML(getAmountClass(transaction.type))}">
                ${sanitizeHTML(formatCurrencyForDisplay(transaction))}
            </td>
        </tr>
    `).join('');
}

// Paramètres
async function updateParametresDisplay() {
    updateCategoriesList();
    
    if (appData.settings) {
        document.getElementById('currency-setting').value = appData.settings.currency || 'EUR';
        document.getElementById('org-name').value = appData.settings.org_name || '';
    }
}

function updateCategoriesList() {
    const container = document.getElementById('categories-list');
    
    if (!appData.categories || appData.categories.length === 0) {
        container.innerHTML = '<p class="text-gray-500">Aucune catégorie</p>';
        return;
    }
    
    container.innerHTML = appData.categories.map(category => `
        <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
            <span>${sanitizeHTML(category.name)}</span>
            <button onclick="deleteCategory(${category.id})" class="text-red-600 hover:text-red-800">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `).join('');
}

async function addCategory() {
    const name = document.getElementById('new-category').value.trim();
    
    if (!name) {
        showNotification('Veuillez saisir un nom de catégorie', 'error');
        return;
    }
    
    try {
        await apiCall('/categories.php', {
            method: 'POST',
            body: { name: name }
        });
        
        showNotification('Catégorie ajoutée avec succès', 'success');
        
        await loadAllData();
        updateCategorySelects();
        updateCategoriesList();
        
        document.getElementById('new-category').value = '';
        
    } catch (error) {
        console.error('Erreur lors de l\'ajout de la catégorie:', error);
        showNotification(error.message, 'error');
    }
}

async function deleteCategory(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')) {
        return;
    }
    
    try {
        await apiCall(`/categories.php?id=${id}`, {
            method: 'DELETE'
        });
        
        showNotification('Catégorie supprimée avec succès', 'success');
        
        await loadAllData();
        updateCategorySelects();
        updateCategoriesList();
        
    } catch (error) {
        console.error('Erreur lors de la suppression:', error);
        showNotification(error.message, 'error');
    }
}

async function saveSettings() {
    try {
        const settings = {
            currency: document.getElementById('currency-setting').value,
            org_name: document.getElementById('org-name').value
        };
        
        await apiCall('/settings.php', {
            method: 'PUT',
            body: settings
        });
        
        showNotification('Paramètres sauvegardés avec succès', 'success');
        
        const response = await apiCall('/settings.php');
        appData.settings = response.data || {};
        
    } catch (error) {
        console.error('Erreur lors de la sauvegarde:', error);
        showNotification(error.message, 'error');
    }
}

async function exportData() {
    try {
        window.open(`${API_BASE}/settings.php/export`, '_blank');
        
    } catch (error) {
        console.error('Erreur lors de l\'export:', error);
        showNotification(error.message, 'error');
    }
}

function importData(event) {
    const file = event.target.files[0];
    
    if (!file) {
        return;
    }
    
    if (file.type !== 'application/json') {
        showNotification('Veuillez sélectionner un fichier JSON', 'error');
        return;
    }
    
    const reader = new FileReader();
    reader.onload = async function(e) {
        try {
            const data = JSON.parse(e.target.result);
            
            await apiCall('/settings.php/import', {
                method: 'POST',
                body: { data: data }
            });
            
            showNotification('Données importées avec succès', 'success');
            
            await loadAllData();
            updateAllDisplays();
            
        } catch (error) {
            console.error('Erreur lors de l\'import:', error);
            showNotification(error.message, 'error');
        }
    };
    
    reader.readAsText(file);
}

async function clearAllData() {
    if (!confirm('Êtes-vous sûr de vouloir effacer toutes les données ? Cette action est irréversible.')) {
        return;
    }
    
    try {
        showNotification('Fonctionnalité non implémentée', 'warning');
        
    } catch (error) {
        console.error('Erreur lors de l\'effacement:', error);
        showNotification(error.message, 'error');
    }
}

// Export PDF et Excel
function exportToPDF() {
    showNotification('Fonctionnalité en cours de développement', 'info');
}

function exportToExcel() {
    showNotification('Fonctionnalité en cours de développement', 'info');
}

// Graphiques
function updateCharts(stats, comptes) {
    // Graphique répartition recettes/dépenses
    const ctx1 = document.getElementById('repartitionChart').getContext('2d');
    
    if (charts.repartition) {
        charts.repartition.destroy();
    }
    
    charts.repartition = new Chart(ctx1, {
        type: 'doughnut',
        data: {
            labels: ['Recettes', 'Dépenses'],
            datasets: [{
                data: [stats.total_recettes || 0, stats.total_depenses || 0],
                backgroundColor: ['#10B981', '#EF4444'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // Graphique répartition par compte
    const ctx2 = document.getElementById('comptesChart').getContext('2d');
    
    if (charts.comptes) {
        charts.comptes.destroy();
    }
    
    const comptesData = comptes.map(c => ({
        label: c.name,
        value: Math.abs(parseFloat(c.balance || 0))
    }));
    
    charts.comptes = new Chart(ctx2, {
        type: 'pie',
        data: {
            labels: comptesData.map(c => c.label),
            datasets: [{
                data: comptesData.map(c => c.value),
                backgroundColor: [
                    '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
                    '#06B6D4', '#84CC16', '#F97316', '#EC4899', '#6B7280'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Fonctions utilitaires
function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: appData.settings.currency || 'DZD' // Changé en DZD pour correspondre à tes données
    }).format(amount || 0);
}

function formatDate(dateString) {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('fr-FR');
}

function getTypeClass(type) {
    switch (type) {
        case 'recette':
        case 'virement_credit':
            return 'bg-green-100 text-green-800';
        case 'depense':
        case 'virement_debit':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function getTypeLabel(type) {
    switch (type) {
        case 'recette': return 'Recette';
        case 'depense': return 'Dépense';
        case 'virement_debit': return 'Virement (débit)';
        case 'virement_credit': return 'Virement (crédit)';
        default: return 'Autre';
    }
}

function getAmountClass(type) {
    switch (type) {
        case 'recette':
        case 'virement_credit':
            return 'text-green-600';
        case 'depense':
        case 'virement_debit':
            return 'text-red-600';
        default:
            return '';
    }
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm ${getNotificationClass(type)}`;
    notification.innerHTML = `
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas ${getNotificationIcon(type)}"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium">${message}</p>
            </div>
            <div class="ml-auto pl-3">
                <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

function getNotificationClass(type) {
    switch (type) {
        case 'success': return 'bg-green-100 text-green-800 border border-green-200';
        case 'error': return 'bg-red-100 text-red-800 border border-red-200';
        case 'warning': return 'bg-yellow-100 text-yellow-800 border border-yellow-200';
        default: return 'bg-blue-100 text-blue-800 border border-blue-200';
    }
}

function getNotificationIcon(type) {
    switch (type) {
        case 'success': return 'fa-check-circle';
        case 'error': return 'fa-exclamation-circle';
        case 'warning': return 'fa-exclamation-triangle';
        default: return 'fa-info-circle';
    }
}

// Fonction de rafraîchissement
async function refreshData() {
    try {
        showConnectionStatus('loading', 'Actualisation...');
        
        await loadAllData();
        updateAllDisplays();
        
        if (currentSection === 'dashboard') {
            updateDashboard();
        } else if (currentSection === 'transactions') {
            updateTransactionsDisplay();
        } else if (currentSection === 'tiers') {
            updateTiersDisplay();
        } else if (currentSection === 'banque') {
            updateBanqueDisplay();
        } else if (currentSection === 'caisse') {
            updateCaisseDisplay();
        } else if (currentSection === 'rapports') {
            updateRapportsDisplay();
        } else if (currentSection === 'parametres') {
            updateParametresDisplay();
        }
        
        showConnectionStatus('success', 'À jour');
        showNotification('Données actualisées', 'success');
        
    } catch (error) {
        console.error('Erreur lors de l\'actualisation:', error);
        showConnectionStatus('error', 'Erreur');
        showNotification('Erreur lors de l\'actualisation: ' + error.message, 'error');
    }
}

// Draggable Modals
document.querySelectorAll('.modal').forEach(makeDraggable);

function makeDraggable(modal) {
    const header = modal.querySelector('.modal-header');
    let isDragging = false;
    let offset = { x: 0, y: 0 };

    if (header) {
        header.addEventListener('mousedown', (e) => {
            isDragging = true;
            offset.x = e.clientX - modal.offsetLeft;
            offset.y = e.clientY - modal.offsetTop;
            modal.style.position = 'absolute'; // Necessary for top/left to work
        });
    }

    document.addEventListener('mouseup', () => {
        isDragging = false;
    });

    document.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        e.preventDefault();
        modal.style.left = `${e.clientX - offset.x}px`;
        modal.style.top = `${e.clientY - offset.y}px`;
    });
}

// ====================================
// FILTRES TIERS
// ====================================

// Variables globales pour les filtres tiers
let tiersFilterDebounceTimer = null;
let originalTiersData = { clients: [], fournisseurs: [] };

/**
 * Fonction de débounce pour la recherche textuelle des tiers
 */
function debouncedApplyTiersFilters() {
    clearTimeout(tiersFilterDebounceTimer);
    tiersFilterDebounceTimer = setTimeout(applyTiersFilters, 300);
}

/**
 * Application des filtres sur les tiers
 */
function applyTiersFilters() {
    const searchText = document.getElementById('search-tiers')?.value?.toLowerCase() || '';
    const filterSolde = document.getElementById('filter-tiers-solde')?.value || '';
    const filterPeriode = document.getElementById('filter-tiers-periode')?.value || '';
    const filterStatut = document.getElementById('filter-tiers-statut')?.value || '';
    
    console.log('🔍 Application des filtres tiers:', { searchText, filterSolde, filterPeriode, filterStatut });
    
    // Filtrer les clients
    const filteredClients = filterTiersData(originalTiersData.clients, {
        searchText, filterSolde, filterPeriode, filterStatut
    });
    
    // Filtrer les fournisseurs
    const filteredFournisseurs = filterTiersData(originalTiersData.fournisseurs, {
        searchText, filterSolde, filterPeriode, filterStatut
    });
    
    // Mettre à jour les tableaux
    updateTiersTable('clients-table', filteredClients);
    updateTiersTable('fournisseurs-table', filteredFournisseurs);
    
    // Mettre à jour les statistiques
    updateTiersFilterStats(filteredClients, filteredFournisseurs);
}

/**
 * Filtre les données des tiers selon les critères
 */
function filterTiersData(tiersArray, filters) {
    const { searchText, filterSolde, filterPeriode, filterStatut } = filters;
    
    return tiersArray.filter(tiers => {
        // Filtre recherche textuelle
        if (searchText) {
            const searchFields = [
                tiers.code || '',
                tiers.raison_sociale || '',
                tiers.contact || '',
                tiers.email || '',
                tiers.telephone || '',
                tiers.siret || '',
                tiers.adresse || ''
            ].join(' ').toLowerCase();
            
            if (!searchFields.includes(searchText)) {
                return false;
            }
        }
        
        // Filtre solde
        if (filterSolde) {
            const solde = parseFloat(tiers.solde || 0);
            switch (filterSolde) {
                case 'debiteur':
                    if (solde <= 0) return false;
                    break;
                case 'crediteur':
                    if (solde >= 0) return false;
                    break;
                case 'equilibre':
                    if (solde !== 0) return false;
                    break;
            }
        }
        
        // Filtre période (basé sur created_at)
        if (filterPeriode) {
            const now = new Date();
            const createdAt = new Date(tiers.created_at);
            const daysDiff = Math.floor((now - createdAt) / (1000 * 60 * 60 * 24));
            
            switch (filterPeriode) {
                case 'recent':
                    if (daysDiff > 30) return false;
                    break;
                case 'mois':
                    if (createdAt.getMonth() !== now.getMonth() || 
                        createdAt.getFullYear() !== now.getFullYear()) return false;
                    break;
                case 'trimestre':
                    const currentQuarter = Math.floor(now.getMonth() / 3);
                    const createdQuarter = Math.floor(createdAt.getMonth() / 3);
                    if (createdQuarter !== currentQuarter || 
                        createdAt.getFullYear() !== now.getFullYear()) return false;
                    break;
                case 'ancien':
                    if (daysDiff <= 90) return false;
                    break;
            }
        }
        
        // Filtre statut
        if (filterStatut) {
            const isActive = tiers.is_active !== false; // Par défaut actif si non spécifié
            switch (filterStatut) {
                case 'actif':
                    if (!isActive) return false;
                    break;
                case 'inactif':
                    if (isActive) return false;
                    break;
            }
        }
        
        return true;
    });
}

/**
 * Met à jour les statistiques des filtres tiers
 */
function updateTiersFilterStats(filteredClients, filteredFournisseurs) {
    const totalFiltered = filteredClients.length + filteredFournisseurs.length;
    const totalOriginal = originalTiersData.clients.length + originalTiersData.fournisseurs.length;
    
    // Calculer le solde total des tiers filtrés
    const soldeTotal = [...filteredClients, ...filteredFournisseurs]
        .reduce((sum, tiers) => sum + parseFloat(tiers.solde || 0), 0);
    
    // Mettre à jour les éléments d'affichage
    const totalElement = document.getElementById('tiers-count-total');
    const filteredElement = document.getElementById('tiers-count-filtered');
    const soldeElement = document.getElementById('tiers-solde-total');
    
    if (totalElement) {
        totalElement.innerHTML = `<i class="fas fa-users mr-1 text-blue-600"></i>Total: <strong class="ml-1">${totalOriginal}</strong>`;
    }
    
    if (filteredElement) {
        const color = totalFiltered === totalOriginal ? 'text-gray-600' : 'text-green-600';
        filteredElement.innerHTML = `<i class="fas fa-filter mr-1 ${color}"></i>Affichés: <strong class="ml-1">${totalFiltered}</strong>`;
    }
    
    if (soldeElement) {
        const color = soldeTotal >= 0 ? 'text-green-600' : 'text-red-600';
        soldeElement.innerHTML = `<i class="fas fa-euro-sign mr-1 ${color}"></i>Solde total: <strong class="ml-1">${formatCurrency(soldeTotal)}</strong>`;
    }
}

/**
 * Efface tous les filtres tiers
 */
function clearTiersFilters() {
    // Réinitialiser tous les champs de filtre
    document.getElementById('search-tiers').value = '';
    document.getElementById('filter-tiers-solde').value = '';
    document.getElementById('filter-tiers-periode').value = '';
    document.getElementById('filter-tiers-statut').value = '';
    
    // Réappliquer les filtres (maintenant vides)
    applyTiersFilters();
    
    showNotification('Filtres effacés', 'success');
}

/**
 * Initialise les données originales des tiers pour le filtrage
 */
function initializeTiersFilters() {
    // Sauvegarder les données originales
    originalTiersData.clients = [...(appData.clients || [])];
    originalTiersData.fournisseurs = [...(appData.fournisseurs || [])];
    
    console.log('📊 Filtres tiers initialisés:', {
        clients: originalTiersData.clients.length,
        fournisseurs: originalTiersData.fournisseurs.length
    });
    
    // Mettre à jour les statistiques initiales
    updateTiersFilterStats(originalTiersData.clients, originalTiersData.fournisseurs);
}

/**
 * Met à jour l'affichage des tiers avec application automatique des filtres
 */
function updateTiersDisplayWithFilters() {
    // Initialiser les données pour les filtres
    initializeTiersFilters();
    
    // Appliquer les filtres actuels
    applyTiersFilters();
}

// === NOUVELLES FONCTIONS DE FILTRAGE POUR CLIENTS ET FOURNISSEURS ===

// Variables pour les données originales
let originalClientsData = [];
let originalFournisseursData = [];
let clientsFilterDebounceTimer = null;
let fournisseursFilterDebounceTimer = null;

/**
 * Fonctions de filtrage pour les CLIENTS
 */
function debouncedApplyClientsFilters() {
    clearTimeout(clientsFilterDebounceTimer);
    clientsFilterDebounceTimer = setTimeout(applyClientsFilters, 300);
}

function applyClientsFilters() {
    console.log('🔄 Application des filtres clients');
    
    try {
        // Récupérer les valeurs des filtres
        const searchTerm = document.getElementById('search-clients').value.toLowerCase();
        const soldeFilter = document.getElementById('filter-clients-solde').value;
        const periodeFilter = document.getElementById('filter-clients-periode').value;
        const statutFilter = document.getElementById('filter-clients-statut').value;
        
        console.log('📋 Filtres clients:', { searchTerm, soldeFilter, periodeFilter, statutFilter });
        
        // Filtrer les données
        let filteredClients = [...originalClientsData];
        
        // Filtre de recherche textuelle
        if (searchTerm) {
            filteredClients = filteredClients.filter(client => {
                const searchableText = [
                    client.code || '',
                    client.raison_sociale || '',
                    client.contact || '',
                    client.email || '',
                    client.telephone || ''
                ].join(' ').toLowerCase();
                
                return searchableText.includes(searchTerm);
            });
        }
        
        // Filtre par solde
        if (soldeFilter) {
            filteredClients = filteredClients.filter(client => {
                const solde = parseFloat(client.solde) || 0;
                switch (soldeFilter) {
                    case 'debiteur': return solde > 0;
                    case 'crediteur': return solde < 0;
                    case 'equilibre': return solde === 0;
                    default: return true;
                }
            });
        }
        
        console.log(`✅ ${filteredClients.length} clients après filtrage`);
        
        // Mettre à jour la table
        updateClientsTable(filteredClients);
        
        // Mettre à jour les statistiques
        updateClientsFilterStats(filteredClients);
        
    } catch (error) {
        console.error('❌ Erreur lors du filtrage clients:', error);
    }
}

function clearClientsFilters() {
    console.log('🧹 Effacement des filtres clients');
    
    // Vider tous les champs de filtre
    document.getElementById('search-clients').value = '';
    document.getElementById('filter-clients-solde').value = '';
    document.getElementById('filter-clients-periode').value = '';
    document.getElementById('filter-clients-statut').value = '';
    
    // Réappliquer les filtres (maintenant vides)
    applyClientsFilters();
    
    showNotification('Filtres clients effacés', 'success');
}

function initializeClientsFilters() {
    // Charger les données clients depuis l'API
    loadClientsData().then(() => {
        // Sauvegarder les données originales
        originalClientsData = [...(appData.clients || [])];
        
        console.log('📊 Filtres clients initialisés:', {
            clients: originalClientsData.length
        });
        
        // Mettre à jour les statistiques initiales
        updateClientsFilterStats(originalClientsData);
    });
}

function updateClientsTable(clients) {
    const tbody = document.getElementById('clients-table');
    
    if (!tbody) {
        console.error('❌ Table clients non trouvée');
        return;
    }
    
    if (!clients || clients.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-8 text-gray-500"><i class="fas fa-user-friends text-4xl mb-2 block text-gray-300"></i>Aucun client trouvé</td></tr>';
        return;
    }
    
    tbody.innerHTML = clients.map(client => {
        const safeId = sanitizeHTML(client.id || '');
        if (!safeId) return '';
        
        return `
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-6 py-4 border-b border-gray-200">${sanitizeHTML(client.code || '')}</td>
            <td class="px-6 py-4 border-b border-gray-200 font-medium">${sanitizeHTML(client.raison_sociale || '')}</td>
            <td class="px-6 py-4 border-b border-gray-200">${sanitizeHTML(client.contact || '')}</td>
            <td class="px-6 py-4 border-b border-gray-200">${sanitizeHTML(client.telephone || '')}</td>
            <td class="px-6 py-4 border-b border-gray-200">${sanitizeHTML(client.email || '')}</td>
            <td class="px-6 py-4 border-b border-gray-200 text-right">
                <span class="${parseFloat(client.solde) >= 0 ? 'text-green-600' : 'text-red-600'} font-medium">
                    ${formatCurrency(client.solde)}
                </span>
            </td>
            <td class="px-6 py-4 border-b border-gray-200 text-center no-print">
                <button onclick="editTiers('${safeId}', 'client')" class="text-blue-600 hover:text-blue-800 mr-2" title="Modifier">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="deleteTiers('${safeId}', 'client')" class="text-red-600 hover:text-red-800" title="Supprimer">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>`;
    }).join('');
}

function updateClientsFilterStats(clients) {
    // Mettre à jour les compteurs
    document.getElementById('clients-count-total').innerHTML = `
        <i class="fas fa-user-friends mr-1 text-blue-600"></i>
        Total clients: <strong class="ml-1">${originalClientsData.length}</strong>
    `;
    
    document.getElementById('clients-count-filtered').innerHTML = `
        <i class="fas fa-filter mr-1 text-green-600"></i>
        Affichés: <strong class="ml-1">${clients.length}</strong>
    `;
    
    // Calculer le solde total
    const soldeTotal = clients.reduce((sum, client) => sum + (parseFloat(client.solde) || 0), 0);
    document.getElementById('clients-solde-total').innerHTML = `
        <i class="fas fa-euro-sign mr-1 text-purple-600"></i>
        Solde total: <strong class="ml-1">${formatCurrency(soldeTotal)}</strong>
    `;
}

/**
 * Fonctions de filtrage pour les FOURNISSEURS
 */
function debouncedApplyFournisseursFilters() {
    clearTimeout(fournisseursFilterDebounceTimer);
    fournisseursFilterDebounceTimer = setTimeout(applyFournisseursFilters, 300);
}

function applyFournisseursFilters() {
    console.log('🔄 Application des filtres fournisseurs');
    
    try {
        // Récupérer les valeurs des filtres
        const searchTerm = document.getElementById('search-fournisseurs').value.toLowerCase();
        const soldeFilter = document.getElementById('filter-fournisseurs-solde').value;
        const periodeFilter = document.getElementById('filter-fournisseurs-periode').value;
        const statutFilter = document.getElementById('filter-fournisseurs-statut').value;
        
        console.log('📋 Filtres fournisseurs:', { searchTerm, soldeFilter, periodeFilter, statutFilter });
        
        // Filtrer les données
        let filteredFournisseurs = [...originalFournisseursData];
        
        // Filtre de recherche textuelle
        if (searchTerm) {
            filteredFournisseurs = filteredFournisseurs.filter(fournisseur => {
                const searchableText = [
                    fournisseur.code || '',
                    fournisseur.raison_sociale || '',
                    fournisseur.contact || '',
                    fournisseur.email || '',
                    fournisseur.telephone || ''
                ].join(' ').toLowerCase();
                
                return searchableText.includes(searchTerm);
            });
        }
        
        // Filtre par solde
        if (soldeFilter) {
            filteredFournisseurs = filteredFournisseurs.filter(fournisseur => {
                const solde = parseFloat(fournisseur.solde) || 0;
                switch (soldeFilter) {
                    case 'debiteur': return solde > 0;
                    case 'crediteur': return solde < 0;
                    case 'equilibre': return solde === 0;
                    default: return true;
                }
            });
        }
        
        console.log(`✅ ${filteredFournisseurs.length} fournisseurs après filtrage`);
        
        // Mettre à jour la table
        updateFournisseursTable(filteredFournisseurs);
        
        // Mettre à jour les statistiques
        updateFournisseursFilterStats(filteredFournisseurs);
        
    } catch (error) {
        console.error('❌ Erreur lors du filtrage fournisseurs:', error);
    }
}

function clearFournisseursFilters() {
    console.log('🧹 Effacement des filtres fournisseurs');
    
    // Vider tous les champs de filtre
    document.getElementById('search-fournisseurs').value = '';
    document.getElementById('filter-fournisseurs-solde').value = '';
    document.getElementById('filter-fournisseurs-periode').value = '';
    document.getElementById('filter-fournisseurs-statut').value = '';
    
    // Réappliquer les filtres (maintenant vides)
    applyFournisseursFilters();
    
    showNotification('Filtres fournisseurs effacés', 'success');
}

function initializeFournisseursFilters() {
    // Charger les données fournisseurs depuis l'API
    loadFournisseursData().then(() => {
        // Sauvegarder les données originales
        originalFournisseursData = [...(appData.fournisseurs || [])];
        
        console.log('📊 Filtres fournisseurs initialisés:', {
            fournisseurs: originalFournisseursData.length
        });
        
        // Mettre à jour les statistiques initiales
        updateFournisseursFilterStats(originalFournisseursData);
    });
}

function updateFournisseursTable(fournisseurs) {
    const tbody = document.getElementById('fournisseurs-table');
    
    if (!tbody) {
        console.error('❌ Table fournisseurs non trouvée');
        return;
    }
    
    if (!fournisseurs || fournisseurs.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-8 text-gray-500"><i class="fas fa-truck text-4xl mb-2 block text-gray-300"></i>Aucun fournisseur trouvé</td></tr>';
        return;
    }
    
    tbody.innerHTML = fournisseurs.map(fournisseur => {
        const safeId = sanitizeHTML(fournisseur.id || '');
        if (!safeId) return '';
        
        return `
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-6 py-4 border-b border-gray-200">${sanitizeHTML(fournisseur.code || '')}</td>
            <td class="px-6 py-4 border-b border-gray-200 font-medium">${sanitizeHTML(fournisseur.raison_sociale || '')}</td>
            <td class="px-6 py-4 border-b border-gray-200">${sanitizeHTML(fournisseur.contact || '')}</td>
            <td class="px-6 py-4 border-b border-gray-200">${sanitizeHTML(fournisseur.telephone || '')}</td>
            <td class="px-6 py-4 border-b border-gray-200">${sanitizeHTML(fournisseur.email || '')}</td>
            <td class="px-6 py-4 border-b border-gray-200 text-right">
                <span class="${parseFloat(fournisseur.solde) >= 0 ? 'text-green-600' : 'text-red-600'} font-medium">
                    ${formatCurrency(fournisseur.solde)}
                </span>
            </td>
            <td class="px-6 py-4 border-b border-gray-200 text-center no-print">
                <button onclick="editTiers('${safeId}', 'fournisseur')" class="text-green-600 hover:text-green-800 mr-2" title="Modifier">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="deleteTiers('${safeId}', 'fournisseur')" class="text-red-600 hover:text-red-800" title="Supprimer">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>`;
    }).join('');
}

function updateFournisseursFilterStats(fournisseurs) {
    // Mettre à jour les compteurs
    document.getElementById('fournisseurs-count-total').innerHTML = `
        <i class="fas fa-truck mr-1 text-green-600"></i>
        Total fournisseurs: <strong class="ml-1">${originalFournisseursData.length}</strong>
    `;
    
    document.getElementById('fournisseurs-count-filtered').innerHTML = `
        <i class="fas fa-filter mr-1 text-green-600"></i>
        Affichés: <strong class="ml-1">${fournisseurs.length}</strong>
    `;
    
    // Calculer le solde total
    const soldeTotal = fournisseurs.reduce((sum, fournisseur) => sum + (parseFloat(fournisseur.solde) || 0), 0);
    document.getElementById('fournisseurs-solde-total').innerHTML = `
        <i class="fas fa-euro-sign mr-1 text-purple-600"></i>
        Solde total: <strong class="ml-1">${formatCurrency(soldeTotal)}</strong>
    `;
}

/**
 * Fonctions de chargement des données
 */
async function loadClientsData() {
    try {
        console.log('📥 Chargement des données clients...');
        const response = await apiCall('/tiers.php?type=client');
        if (response && response.data) {
            appData.clients = response.data;
            console.log(`✅ ${appData.clients.length} clients chargés`);
        }
    } catch (error) {
        console.error('❌ Erreur lors du chargement des clients:', error);
        appData.clients = [];
    }
}

async function loadFournisseursData() {
    try {
        console.log('📥 Chargement des données fournisseurs...');
        const response = await apiCall('/tiers.php?type=fournisseur');
        if (response && response.data) {
            appData.fournisseurs = response.data;
            console.log(`✅ ${appData.fournisseurs.length} fournisseurs chargés`);
        }
    } catch (error) {
        console.error('❌ Erreur lors du chargement des fournisseurs:', error);
        appData.fournisseurs = [];
    }
}

// === FONCTIONS DE DIAGNOSTIC MODAL TIERS ===
function diagnosticTiersModal() {
    console.log('🔍 === DIAGNOSTIC MODAL TIERS ===');
    
    // Vérifier les éléments DOM
    const modal = document.getElementById('tiersModal');
    const modalTitle = document.getElementById('tiersModalTitle');
    const form = document.getElementById('tiersForm');
    const clientBtn = document.querySelector('button[onclick*="openTiersModal(\'client\')"]');
    const fournisseurBtn = document.querySelector('button[onclick*="openTiersModal(\'fournisseur\')"]');
    
    console.log('📋 Éléments DOM:');
    console.log('- Modal:', !!modal, modal ? 'OK' : 'MANQUANT');
    console.log('- Titre modal:', !!modalTitle, modalTitle ? 'OK' : 'MANQUANT');
    console.log('- Formulaire:', !!form, form ? 'OK' : 'MANQUANT');
    console.log('- Bouton client:', !!clientBtn, clientBtn ? 'OK' : 'MANQUANT');
    console.log('- Bouton fournisseur:', !!fournisseurBtn, fournisseurBtn ? 'OK' : 'MANQUANT');
    
    if (modal) {
        console.log('📐 Styles de la modal:');
        const computedStyle = window.getComputedStyle(modal);
        console.log('- Display:', computedStyle.display);
        console.log('- Position:', computedStyle.position);
        console.log('- Z-index:', computedStyle.zIndex);
        console.log('- Visibility:', computedStyle.visibility);
    }
    
    console.log('🔍 === FIN DIAGNOSTIC ===');
}

function testTiersModals() {
    console.log('🧪 === TEST DES MODALS TIERS ===');
    
    // Test modal client
    console.log('📋 Test modal client...');
    try {
        openTiersModal('client');
        setTimeout(() => {
            const modal = document.getElementById('tiersModal');
            if (modal && modal.style.display === 'block') {
                console.log('✅ Modal client s\'ouvre correctement');
                closeTiersModal();
            } else {
                console.log('❌ Modal client ne s\'ouvre pas');
            }
        }, 100);
    } catch (error) {
        console.error('❌ Erreur lors de l\'ouverture modal client:', error);
    }
    
    // Test modal fournisseur après un délai
    setTimeout(() => {
        console.log('📋 Test modal fournisseur...');
        try {
            openTiersModal('fournisseur');
            setTimeout(() => {
                const modal = document.getElementById('tiersModal');
                if (modal && modal.style.display === 'block') {
                    console.log('✅ Modal fournisseur s\'ouvre correctement');
                    closeTiersModal();
                } else {
                    console.log('❌ Modal fournisseur ne s\'ouvre pas');
                }
            }, 100);
        } catch (error) {
            console.error('❌ Erreur lors de l\'ouverture modal fournisseur:', error);
        }
    }, 500);
    
    console.log('🧪 === FIN TEST ===');
}

// === FONCTIONS DE DIAGNOSTIC ONGLETS ===
function diagnosticTiersTab() {
    console.log('🔍 === DIAGNOSTIC ONGLETS TIERS ===');
    
    // Vérifier tous les boutons d'onglets
    const tabButtons = [
        'tab-identite', 'tab-generales', 'tab-contact', 
        'tab-legal', 'tab-compta', 'tab-dates', 'tab-autres'
    ];
    
    console.log('📋 État des boutons d\'onglets:');
    tabButtons.forEach(tabId => {
        const tab = document.getElementById(tabId);
        if (tab) {
            const isActive = tab.classList.contains('active');
            const hasBlueClasses = tab.classList.contains('border-blue-500') && tab.classList.contains('text-blue-600');
            console.log(`- ${tabId}: EXISTE, Actif: ${isActive}, Style actif: ${hasBlueClasses}`);
        } else {
            console.log(`- ${tabId}: MANQUANT ❌`);
        }
    });
    
    // Vérifier tous les contenus d'onglets
    const tabContents = [
        'content-identite', 'content-generales', 'content-contact',
        'content-legal', 'content-compta', 'content-dates', 'content-autres'
    ];
    
    console.log('📋 État des contenus d\'onglets:');
    tabContents.forEach(contentId => {
        const content = document.getElementById(contentId);
        if (content) {
            const isHidden = content.classList.contains('hidden');
            const computedStyle = window.getComputedStyle(content);
            console.log(`- ${contentId}: EXISTE, Caché: ${isHidden}, Display: ${computedStyle.display}`);
        } else {
            console.log(`- ${contentId}: MANQUANT ❌`);
        }
    });
    
    console.log('🔍 === FIN DIAGNOSTIC ONGLETS ===');
}

function fixTiersTabsDisplay() {
    console.log('🔧 === CORRECTION AFFICHAGE ONGLETS ===');
    
    // S'assurer que tous les onglets ont les bonnes classes CSS
    const allTabs = document.querySelectorAll('.tiers-tab');
    console.log(`📋 Correction de ${allTabs.length} onglets`);
    
    allTabs.forEach(tab => {
        if (!tab.classList.contains('border-transparent')) {
            tab.classList.add('border-transparent');
        }
        if (!tab.classList.contains('text-gray-500')) {
            tab.classList.add('text-gray-500');
        }
    });
    
    // S'assurer que tous les contenus ont la classe tiers-tab-content
    const allContents = document.querySelectorAll('.tiers-tab-content');
    console.log(`📋 Correction de ${allContents.length} contenus`);
    
    allContents.forEach(content => {
        if (!content.classList.contains('hidden')) {
            content.classList.add('hidden');
        }
    });
    
    // Activer l'onglet identité par défaut
    switchTiersTab('identite');
    
    console.log('✅ Correction terminée');
    console.log('🔧 === FIN CORRECTION ===');
}

function testAllTiersTabs() {
    console.log('🧪 === TEST TOUS LES ONGLETS ===');
    
    const tabs = ['identite', 'generales', 'contact', 'legal', 'compta', 'dates', 'autres'];
    let index = 0;
    
    function testNextTab() {
        if (index < tabs.length) {
            const tabName = tabs[index];
            console.log(`🧪 Test onglet: ${tabName}`);
            switchTiersTab(tabName);
            
            setTimeout(() => {
                const content = document.getElementById(`content-${tabName}`);
                if (content && !content.classList.contains('hidden')) {
                    console.log(`✅ Onglet ${tabName} fonctionne`);
                } else {
                    console.log(`❌ Onglet ${tabName} ne fonctionne pas`);
                }
                
                index++;
                testNextTab();
            }, 200);
        } else {
            console.log('🧪 === FIN TEST ONGLETS ===');
            // Revenir à l'onglet identité
            switchTiersTab('identite');
        }
    }
    
    testNextTab();
}

// Rendre les fonctions disponibles globalement pour le débogage
window.diagnosticTiersModal = diagnosticTiersModal;
window.testTiersModals = testTiersModals;
window.diagnosticTiersTab = diagnosticTiersTab;
window.fixTiersTabsDisplay = fixTiersTabsDisplay;
window.testAllTiersTabs = testAllTiersTabs;

// ========== FONCTIONS POUR LES ONGLETS BANQUE/CAISSE ==========

/**
 * Affiche un onglet spécifique de la section Banque
 * @param {string} tabName - Nom de l'onglet à afficher
 */
function showBanqueTab(tabName) {
    console.log(`🏦 Affichage onglet Banque: ${tabName}`);
    
    // Désactiver tous les onglets Banque
    const tabs = document.querySelectorAll('.banque-tab');
    tabs.forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Masquer tous les contenus Banque
    const contents = document.querySelectorAll('#banque-section .tab-content');
    contents.forEach(content => {
        content.classList.remove('active');
    });
    
    // Activer l'onglet cliqué
    const activeTab = document.querySelector(`.banque-tab[onclick="showBanqueTab('${tabName}')"]`);
    if (activeTab) {
        activeTab.classList.add('active');
    }
    
    // Afficher le contenu correspondant
    const activeContent = document.getElementById(`banque-${tabName}-content`);
    if (activeContent) {
        activeContent.classList.add('active');
        
        // Si c'est l'onglet historique, charger les données si nécessaire
        if (tabName === 'historique') {
            loadBanqueHistorique();
        }
    }
    
    console.log(`✅ Onglet Banque ${tabName} activé`);
}

/**
 * Affiche un onglet spécifique de la section Caisse
 * @param {string} tabName - Nom de l'onglet à afficher
 */
function showCaisseTab(tabName) {
    console.log(`💰 Affichage onglet Caisse: ${tabName}`);
    
    // Désactiver tous les onglets Caisse
    const tabs = document.querySelectorAll('.caisse-tab');
    tabs.forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Masquer tous les contenus Caisse
    const contents = document.querySelectorAll('#caisse-section .tab-content');
    contents.forEach(content => {
        content.classList.remove('active');
    });
    
    // Activer l'onglet cliqué
    const activeTab = document.querySelector(`.caisse-tab[onclick="showCaisseTab('${tabName}')"]`);
    if (activeTab) {
        activeTab.classList.add('active');
    }
    
    // Afficher le contenu correspondant
    const activeContent = document.getElementById(`caisse-${tabName}-content`);
    if (activeContent) {
        activeContent.classList.add('active');
        
        // Si c'est l'onglet historique, charger les données si nécessaire
        if (tabName === 'historique') {
            loadCaisseHistorique();
        }
    }
    
    console.log(`✅ Onglet Caisse ${tabName} activé`);
}

/**
 * Afficher un onglet spécifique dans la section Achats
 * @param {string} tabName - Nom de l'onglet à afficher
 */
function showAchatsTab(tabName) {
    console.log(`🛒 Affichage onglet Achats: ${tabName}`);
    
    try {
        // Désactiver tous les onglets Achats
        const tabs = document.querySelectorAll('.achats-tab');
        tabs.forEach(tab => {
            tab.classList.remove('active');
        });
        
        // Masquer tous les contenus Achats (avec style inline pour être sûr)
        const contents = document.querySelectorAll('#achats-section .tab-content');
        contents.forEach(content => {
            content.classList.remove('active');
            content.style.display = 'none';
        });
        
        // Activer l'onglet cliqué avec une méthode plus robuste
        const activeTab = document.querySelector(`button[onclick="showAchatsTab('${tabName}')"]`);
        if (activeTab) {
            activeTab.classList.add('active');
        } else {
            console.error(`Onglet non trouvé: ${tabName}`);
            return;
        }
        
        // Afficher le contenu correspondant
        const activeContent = document.getElementById(`achats-${tabName}-content`);
        if (activeContent) {
            activeContent.classList.add('active');
            activeContent.style.display = 'block';
            
            // Charger les données spécifiques selon l'onglet
            if (tabName === 'vue-ensemble') {
                if (typeof loadAchatsVueEnsemble === 'function') {
                    loadAchatsVueEnsemble();
                }
            } else if (tabName === 'enregistrements') {
                if (typeof loadAchatsEnregistrements === 'function') {
                    loadAchatsEnregistrements();
                }
            } else if (tabName === 'suivi-paiements') {
                if (typeof loadAchatsSuiviPaiements === 'function') {
                    loadAchatsSuiviPaiements();
                }
            } else if (tabName === 'categories') {
                if (typeof loadAchatsCategories === 'function') {
                    loadAchatsCategories();
                } else if (typeof loadCategoriesList === 'function') {
                    setTimeout(() => {
                        loadCategoriesList();
                    }, 500);
                }
            } else if (tabName === 'rapports') {
                if (typeof loadAchatsRapports === 'function') {
                    loadAchatsRapports();
                } else {
                    console.log('📊 Fonction loadAchatsRapports non trouvée - données par défaut');
                }
            }
        } else {
            console.error(`Contenu non trouvé: achats-${tabName}-content`);
            return;
        }
        
        console.log(`✅ Onglet Achats ${tabName} activé avec succès`);
    } catch (error) {
        console.error('Erreur dans showAchatsTab:', error);
    }
}

/**
 * Charge l'historique des comptes bancaires
 */
async function loadBanqueHistorique() {
    console.log('🔄 Chargement historique bancaire...');
    
    const tbody = document.getElementById('banque-transactions');
    if (!tbody) return;
    
    // Afficher le loading
    tbody.innerHTML = `
        <tr>
            <td colspan="7" class="text-center py-8">
                <div class="loading mx-auto"></div>
                <p class="text-gray-500 mt-3 loading-text">Chargement de l'historique bancaire...</p>
            </td>
        </tr>
    `;
    
    try {
        // Simuler un délai de chargement (remplacer par vraie API)
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        // Données d'exemple (remplacer par vraies données)
        const sampleData = [
            {
                date: '15/10/2025',
                compte: 'Compte Principal',
                description: 'Virement reçu - Salaire',
                type: 'Crédit',
                montant: '+2,500.00 €',
                solde: '15,234.50 €',
                montantClass: 'text-green-600'
            },
            {
                date: '14/10/2025',
                compte: 'Compte Principal',
                description: 'Prélèvement - Assurance',
                type: 'Débit',
                montant: '-125.30 €',
                solde: '12,734.50 €',
                montantClass: 'text-red-600'
            },
            {
                date: '13/10/2025',
                compte: 'Compte Épargne',
                description: 'Virement interne',
                type: 'Crédit',
                montant: '+500.00 €',
                solde: '5,500.00 €',
                montantClass: 'text-blue-600'
            },
            {
                date: '12/10/2025',
                compte: 'Compte Principal',
                description: 'Achat par carte - Supermarché',
                type: 'Débit',
                montant: '-87.45 €',
                solde: '12,859.80 €',
                montantClass: 'text-red-600'
            },
            {
                date: '11/10/2025',
                compte: 'Compte Principal',
                description: 'Dépôt espèces',
                type: 'Crédit',
                montant: '+450.00 €',
                solde: '12,947.25 €',
                montantClass: 'text-green-600'
            }
        ];
        
        // Générer le HTML du tableau
        tbody.innerHTML = sampleData.map(row => `
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-4 py-3">${row.date}</td>
                <td class="px-4 py-3 font-medium">${row.compte}</td>
                <td class="px-4 py-3">${row.description}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded-full text-xs font-semibold ${row.type === 'Crédit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                        ${row.type}
                    </span>
                </td>
                <td class="px-4 py-3 text-right font-semibold ${row.montantClass}">${row.montant}</td>
                <td class="px-4 py-3 text-right font-bold">${row.solde}</td>
                <td class="px-4 py-3 text-center no-print">
                    <button class="text-blue-600 hover:text-blue-800 mx-1" title="Voir détails">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="text-green-600 hover:text-green-800 mx-1" title="Modifier">
                        <i class="fas fa-edit"></i>
                    </button>
                </td>
            </tr>
        `).join('');
        
        console.log('✅ Historique bancaire chargé');
        
    } catch (error) {
        console.error('❌ Erreur chargement historique bancaire:', error);
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-8">
                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl mb-2"></i>
                    <p class="text-red-500">Erreur lors du chargement de l'historique</p>
                </td>
            </tr>
        `;
    }
}

/**
 * Charge les opérations de caisse depuis la base de données (même table que transactions)
 */
async function loadCaisseOperations(filters = {}) {
    try {
        const params = new URLSearchParams();
        Object.keys(filters).forEach(key => {
            if (filters[key]) {
                params.append(key, filters[key]);
            }
        });
        
        // Utiliser la même API que les transactions pour charger depuis la vraie table
        const response = await apiCall(`/transactions.php?${params.toString()}`);
        let allTransactions = response.data || [];
        
        // Filtrer pour ne garder que les opérations des comptes de type 'caisse' (exclure les banques)
        const caisseOperations = allTransactions.filter(transaction => {
            // Vérifier le type de compte associé à la transaction
            const compte = appData.comptes.find(c => c.id === transaction.account_id);
            return compte && compte.type === 'caisse';
        });
        
        appData.caisseOperations = caisseOperations;
        return appData.caisseOperations;
        
    } catch (error) {
        console.error('Erreur lors du chargement des opérations de caisse:', error);
        const errorMessage = error.message || error.toString() || 'Erreur inconnue lors du chargement des opérations de caisse';
        throw new Error(errorMessage);
    }
}

/**
 * Met à jour l'affichage de l'historique des caisses
 */
async function updateCaisseHistoriqueDisplay() {
    try {
        const filters = getCaisseHistoriqueFilters();
        const operations = await loadCaisseOperations(filters);
        updateCaisseHistoriqueTable(operations);
        
    } catch (error) {
        console.error('Erreur lors de la mise à jour de l\'historique des caisses:', error);
        const errorMessage = error.message || error.toString() || 'Erreur inconnue';
        showNotification('Erreur lors du chargement de l\'historique des caisses: ' + errorMessage, 'error');
    }
}

/**
 * Récupère les filtres de l'historique des caisses
 */
function getCaisseHistoriqueFilters() {
    try {
        return {
            search: document.getElementById('search-caisse-historique')?.value?.trim() || '',
            type: document.getElementById('filter-caisse-type')?.value || '',
            account_id: document.getElementById('filter-caisse-account')?.value || '',
            tiers_id: document.getElementById('filter-caisse-tiers')?.value || '',
            category_id: document.getElementById('filter-caisse-category')?.value || '',
            month: document.getElementById('filter-caisse-month')?.value || ''
        };
    } catch (error) {
        console.warn('Erreur lors de la récupération des filtres caisses:', error);
        return { search: '', type: '', account_id: '', tiers_id: '', category_id: '', month: '' };
    }
}

/**
 * Met à jour le tableau de l'historique des caisses
 */
function updateCaisseHistoriqueTable(operations) {
    const tbody = document.getElementById('caisse-historique-table');
    
    if (!operations || operations.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-gray-500">Aucune opération de caisse trouvée</td></tr>';
        return;
    }
    
    tbody.innerHTML = operations.map(operation => `
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-4 py-3">${sanitizeHTML(formatDate(operation.date))}</td>
            <td class="px-4 py-3">
                <span class="px-2 py-1 rounded text-xs ${sanitizeHTML(getTypeClass(operation.type))}">
                    ${sanitizeHTML(getTypeLabel(operation.type))}
                </span>
            </td>
            <td class="px-4 py-3">${sanitizeHTML(operation.description)}</td>
            <td class="px-4 py-3">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs ${getCaisseClass(operation.account_name)}">
                    <span class="w-2 h-2 ${getCaisseColorDot(operation.account_name)} rounded-full mr-1"></span>
                    ${sanitizeHTML(operation.account_name || 'N/A')}
                </span>
            </td>
            <td class="px-4 py-3 text-sm text-gray-600">${sanitizeHTML(operation.tiers_name || '-')}</td>
            <td class="px-4 py-3 text-sm text-gray-600">${sanitizeHTML(operation.category_name || '-')}</td>
            <td class="px-4 py-3 text-right ${sanitizeHTML(getAmountClass(operation.type))}">
                ${sanitizeHTML(formatCurrency(operation.amount))}
            </td>
            <td class="px-4 py-3 text-right font-bold">
                ${operation.balance_after ? sanitizeHTML(formatCurrency(operation.balance_after)) : '-'}
            </td>
            <td class="px-4 py-3 text-center no-print">
                <div class="flex justify-center space-x-1">
                    <button onclick="viewTransactionDetails(${JSON.stringify(operation).replace(/"/g, '&quot;')})" class="text-green-600 hover:text-green-800 p-1 rounded transition-colors" title="Visualiser les détails">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button onclick="editCaisseOperation('${operation.id}')" class="text-blue-600 hover:text-blue-800 p-1 rounded transition-colors" title="Voir/Modifier">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteCaisseOperation('${operation.id}')" class="text-red-600 hover:text-red-800 p-1 rounded transition-colors" title="Supprimer">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

/**
 * Retourne la classe CSS pour l'affichage des caisses/comptes
 */
function getCaisseClass(accountName) {
    if (!accountName) return 'bg-gray-100 text-gray-800';
    
    const name = accountName.toLowerCase();
    
    // Correspondances exactes
    const exactClasses = {
        'caisse principale': 'bg-orange-100 text-orange-800',
        'petite caisse': 'bg-amber-100 text-amber-800',
        'caisse mobile': 'bg-purple-100 text-purple-800',
        'caisse secondaire': 'bg-gray-100 text-gray-800'
    };
    
    if (exactClasses[name]) {
        return exactClasses[name];
    }
    
    // Correspondances par mots-clés
    if (name.includes('caisse')) {
        if (name.includes('principale') || name.includes('main')) return 'bg-orange-100 text-orange-800';
        if (name.includes('petite') || name.includes('small')) return 'bg-amber-100 text-amber-800';
        if (name.includes('mobile') || name.includes('portable')) return 'bg-purple-100 text-purple-800';
        if (name.includes('secondaire') || name.includes('secondary')) return 'bg-gray-100 text-gray-800';
        return 'bg-blue-100 text-blue-800'; // Caisse générique
    }
    
    if (name.includes('banque') || name.includes('bank')) return 'bg-green-100 text-green-800';
    if (name.includes('crédit') || name.includes('credit')) return 'bg-indigo-100 text-indigo-800';
    
    return 'bg-gray-100 text-gray-800'; // Par défaut
}

/**
 * Retourne la classe CSS pour le point coloré des caisses/comptes
 */
function getCaisseColorDot(accountName) {
    if (!accountName) return 'bg-gray-500';
    
    const name = accountName.toLowerCase();
    
    // Correspondances exactes
    const exactColors = {
        'caisse principale': 'bg-orange-500',
        'petite caisse': 'bg-amber-500',
        'caisse mobile': 'bg-purple-500',
        'caisse secondaire': 'bg-gray-500'
    };
    
    if (exactColors[name]) {
        return exactColors[name];
    }
    
    // Correspondances par mots-clés
    if (name.includes('caisse')) {
        if (name.includes('principale') || name.includes('main')) return 'bg-orange-500';
        if (name.includes('petite') || name.includes('small')) return 'bg-amber-500';
        if (name.includes('mobile') || name.includes('portable')) return 'bg-purple-500';
        if (name.includes('secondaire') || name.includes('secondary')) return 'bg-gray-500';
        return 'bg-blue-500'; // Caisse générique
    }
    
    if (name.includes('banque') || name.includes('bank')) return 'bg-green-500';
    if (name.includes('crédit') || name.includes('credit')) return 'bg-indigo-500';
    
    return 'bg-gray-500'; // Par défaut
}

/**
 * Applique les filtres de l'historique des caisses
 */
function applyCaisseHistoriqueFilters() {
    updateCaisseHistoriqueDisplay();
}

/**
 * Variable pour stocker le timer de débounce pour la recherche caisses
 */
let caisseSearchDebounceTimer = null;

/**
 * Fonction de débounce pour la recherche en temps réel des caisses
 */
function debouncedApplyCaisseHistoriqueFilters() {
    if (caisseSearchDebounceTimer) {
        clearTimeout(caisseSearchDebounceTimer);
    }
    
    caisseSearchDebounceTimer = setTimeout(() => {
        applyCaisseHistoriqueFilters();
    }, 300);
}

/**
 * Charge l'historique des caisses (fonction principale appelée depuis l'interface)
 */
async function loadCaisseHistorique() {
    console.log('🔄 Chargement historique des caisses depuis la base de données...');
    
    const tbody = document.getElementById('caisse-historique-table');
    if (!tbody) return;
    
    // Afficher le loading
    tbody.innerHTML = `
        <tr>
            <td colspan="9" class="text-center py-8">
                <div class="loading mx-auto"></div>
                <p class="text-gray-500 mt-3 loading-text">Chargement de l'historique des caisses...</p>
            </td>
        </tr>
    `;
    
    // Mettre à jour les sélecteurs de filtres
    updateAccountSelects();
    updateTiersSelects();
    updateCategorySelects();
    
    // Charger les données réelles depuis la base de données
    await updateCaisseHistoriqueDisplay();
}

/**
 * Initialise les onglets au chargement de la page
 */
function initializeSectionTabs() {
    console.log('🔧 Initialisation des onglets de sections...');
    
    // Charger l'historique bancaire par défaut si la section banque est active
    const banqueSection = document.getElementById('banque-section');
    if (banqueSection && !banqueSection.style.display.includes('none')) {
        loadBanqueHistorique();
    }
    
    // Charger l'historique des caisses par défaut si la section caisse est active
    const caisseSection = document.getElementById('caisse-section');
    if (caisseSection && !caisseSection.style.display.includes('none')) {
        loadCaisseHistorique();
    }
    
    console.log('✅ Onglets de sections initialisés');
}

// ========== GESTION DU MODAL OPÉRATIONS DE CAISSE ==========

/**
 * Modifie une opération de caisse
 */
async function editCaisseOperation(id) {
    console.log('🔍 DÉBUT editCaisseOperation - ID:', id);
    
    try {
        console.log('📡 Appel API pour récupérer opération...');
        const response = await apiCall(`/transactions.php?id=${id}`);
        const operation = response.data;
        console.log('📦 Données reçues:', operation);

        if (!operation) {
            console.log('❌ Opération non trouvée');
            showNotification('Opération de caisse non trouvée.', 'error');
            return;
        }

        editingCaisseOperationId = id;
        console.log('✅ editingCaisseOperationId défini:', editingCaisseOperationId);

        // Pré-remplir le formulaire
        console.log('📝 Pré-remplissage du formulaire...');
        document.getElementById('caisse-operation-caisse').value = operation.account_id;
        document.getElementById('caisse-operation-type').value = operation.type;
        document.getElementById('caisse-operation-description').value = operation.description;
        document.getElementById('caisse-operation-amount').value = operation.amount;
        document.getElementById('caisse-operation-date').value = operation.date;
        document.getElementById('caisse-operation-reference').value = operation.reference || '';
        document.getElementById('caisse-operation-category').value = operation.category_id || '';
        document.getElementById('caisse-operation-tiers').value = operation.tiers_id || '';
        document.getElementById('caisse-operation-notes').value = operation.notes || '';
        console.log('✅ Formulaire pré-rempli');

        // Changer le titre
        console.log('🏷️ Modification du titre...');
        const titleElement = document.querySelector('#caisseOperationModal h3');
        if (titleElement) {
            titleElement.innerHTML = '<i class="fas fa-edit mr-2 text-orange-600"></i>Modifier l\'Opération de Caisse';
            console.log('✅ Titre modifié');
        } else {
            console.error('❌ Élément titre non trouvé');
        }
        
        // Modifier les boutons existants (comme pour les transactions)
        console.log('🔧 Modification des boutons (approche transactions)...');
        
        // Changer le texte du bouton "Valider et fermer" en "Enregistrer les modifications"
        const validateButton = document.querySelector('#caisseOperationModal button[onclick="saveCaisseOperation(false)"]');
        if (validateButton) {
            validateButton.innerHTML = '<i class="fas fa-save mr-2"></i>Enregistrer les modifications';
            // FORCER l'affichage avec CSS agressif
            validateButton.style.cssText = 'display: inline-block !important; visibility: visible !important; opacity: 1 !important; position: relative !important; z-index: 9999 !important; background-color: #f97316 !important; color: white !important;';
            console.log('✅ Bouton "Valider" modifié en "Enregistrer les modifications" avec CSS forcé');
        } else {
            console.error('❌ Bouton "Valider" non trouvé');
        }
        
        // Cacher le bouton "Ajouter et continuer"
        const continueButton = document.querySelector('#caisseOperationModal button[onclick="saveCaisseOperation(true)"]');
        if (continueButton) {
            continueButton.style.display = 'none';
            console.log('✅ Bouton "Ajouter et continuer" caché');
        } else {
            console.error('❌ Bouton "Ajouter et continuer" non trouvé');
        }

        console.log('🚪 Ouverture du modal...');
        // Ouvrir la modale
        document.getElementById('caisseOperationModal').style.display = 'block';
        console.log('✅ Modal ouvert');

    } catch (error) {
        console.error('💥 ERREUR dans editCaisseOperation:', error);
        showNotification('Erreur lors de la récupération de l\'opération.', 'error');
    }
    
    console.log('🏁 FIN editCaisseOperation');
}

/**
 * Supprime une opération de caisse
 */
async function deleteCaisseOperation(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette opération de caisse ?')) {
        return;
    }

    try {
        await apiCall(`/transactions.php?id=${id}`, {
            method: 'DELETE'
        });
        showNotification('Opération de caisse supprimée avec succès.', 'success');
        
        // Recharger l'historique des caisses depuis la base de données
        await updateCaisseHistoriqueDisplay();
        
    } catch (error) {
        console.error('Erreur lors de la suppression de l\'opération de caisse:', error);
        showNotification('Erreur lors de la suppression de l\'opération.', 'error');
    }
}

// Variable pour stocker l'ID de l'opération en cours d'édition
let editingCaisseOperationId = null;

/**
 * Ouvre le modal d'ajout d'opération de caisse
 */
function openCaisseOperationModal() {
    console.log('💰 Ouverture modal opération de caisse');
    
    // Réinitialiser l'ID d'édition
    editingCaisseOperationId = null;
    
    // Réinitialiser le formulaire
    document.getElementById('caisseOperationForm').reset();
    
    // Restaurer le titre et les boutons pour l'ajout
    const titleElement = document.querySelector('#caisseOperationModal h3');
    if (titleElement) {
        titleElement.innerHTML = '<i class="fas fa-cash-register mr-2 text-orange-600"></i>Nouvelle Opération de Caisse';
    }
    
    // Restaurer les boutons pour l'ajout (comme pour les transactions)
    console.log('🔧 Restauration des boutons pour l\'ajout...');
    
    // Restaurer le texte du bouton "Valider"
    const validateButton = document.querySelector('#caisseOperationModal button[onclick="saveCaisseOperation(false)"]');
    if (validateButton) {
        validateButton.innerHTML = '<i class="fas fa-save mr-2"></i>Valider et fermer';
        console.log('✅ Bouton "Valider" restauré');
    } else {
        console.error('❌ Bouton "Valider" non trouvé');
    }
    
    // Afficher le bouton "Ajouter et continuer"
    const continueButton = document.querySelector('#caisseOperationModal button[onclick="saveCaisseOperation(true)"]');
    if (continueButton) {
        continueButton.style.display = 'inline-block';
        console.log('✅ Bouton "Ajouter et continuer" affiché');
    } else {
        console.error('❌ Bouton "Ajouter et continuer" non trouvé');
    }
    
    // Définir la date actuelle par défaut
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('caisse-operation-date').value = today;
    
    // Masquer le résumé par défaut
    document.getElementById('operation-summary').style.display = 'none';
    
    // Afficher le modal
    document.getElementById('caisseOperationModal').style.display = 'block';
    
    // Ajouter les event listeners pour le résumé en temps réel
    addOperationSummaryListeners();
}

/**
 * Ferme le modal d'opération de caisse
 */
function closeCaisseOperationModal() {
    console.log('💰 Fermeture modal opération de caisse');
    document.getElementById('caisseOperationModal').style.display = 'none';
}

/**
 * Ajoute les event listeners pour le résumé en temps réel
 */
function addOperationSummaryListeners() {
    const fields = ['caisse-operation-caisse', 'caisse-operation-type', 'caisse-operation-amount'];
    
    fields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('change', updateOperationSummary);
            field.addEventListener('input', updateOperationSummary);
        }
    });
}

/**
 * Met à jour le résumé de l'opération en temps réel
 */
function updateOperationSummary() {
    const caisse = document.getElementById('caisse-operation-caisse').value;
    const type = document.getElementById('caisse-operation-type').value;
    const amount = parseFloat(document.getElementById('caisse-operation-amount').value) || 0;
    
    const summaryDiv = document.getElementById('operation-summary');
    
    if (caisse && type && amount > 0) {
        // Afficher le résumé
        summaryDiv.style.display = 'block';
        
        // Mettre à jour les valeurs
        document.getElementById('summary-caisse').textContent = getCaisseDisplayName(caisse);
        document.getElementById('summary-type').textContent = getTypeDisplayName(type);
        document.getElementById('summary-amount').textContent = `${amount.toFixed(2)} €`;
        
        // Calculer l'impact sur le solde
        const impact = getOperationImpact(type, amount);
        const impactElement = document.getElementById('summary-impact');
        impactElement.textContent = impact.text;
        impactElement.className = impact.class;
    } else {
        // Masquer le résumé si les champs obligatoires ne sont pas remplis
        summaryDiv.style.display = 'none';
    }
}

/**
 * Retourne le nom d'affichage d'une caisse à partir de son ID
 */
function getCaisseDisplayName(accountId) {
    if (!accountId || !appData.comptes) {
        return 'Caisse Inconnue';
    }
    
    const compte = appData.comptes.find(c => c.id === accountId);
    return compte ? compte.name : 'Caisse Inconnue';
}

/**
 * Retourne le nom d'affichage d'un type d'opération
 */
function getTypeDisplayName(typeId) {
    const names = {
        'encaissement': 'Encaissement',
        'decaissement': 'Décaissement',
        'virement_in': 'Virement entrant',
        'virement_out': 'Virement sortant'
    };
    return names[typeId] || typeId;
}

/**
 * Calcule l'impact de l'opération sur le solde
 */
function getOperationImpact(type, amount) {
    switch (type) {
        case 'encaissement':
        case 'virement_in':
            return {
                text: `+${amount.toFixed(2)} € (Augmentation du solde)`,
                class: 'text-green-600 font-semibold'
            };
        case 'decaissement':
        case 'virement_out':
            return {
                text: `-${amount.toFixed(2)} € (Diminution du solde)`,
                class: 'text-red-600 font-semibold'
            };
        default:
            return {
                text: 'Impact non déterminé',
                class: 'text-gray-600'
            };
    }
}

/**
 * Sauvegarde une opération de caisse
 */
async function saveCaisseOperation(continueAdding = false) {
    console.log('💰 Sauvegarde opération de caisse...');
    
    try {
        // Récupérer les données du formulaire
        const operationData = {
            account_id: document.getElementById('caisse-operation-caisse').value,
            type: document.getElementById('caisse-operation-type').value,
            description: document.getElementById('caisse-operation-description').value,
            amount: parseFloat(document.getElementById('caisse-operation-amount').value),
            date: document.getElementById('caisse-operation-date').value,
            reference: document.getElementById('caisse-operation-reference').value,
            category_id: document.getElementById('caisse-operation-category').value,
            tiers_id: document.getElementById('caisse-operation-tiers').value,
            notes: document.getElementById('caisse-operation-notes').value
        };
        
        // Validation des champs obligatoires
        if (!operationData.account_id || !operationData.type || !operationData.description || 
            !operationData.amount || operationData.amount <= 0 || !operationData.date) {
            showNotification('Veuillez remplir tous les champs obligatoires.', 'warning');
            return;
        }
        
        // Sauvegarde via API
        let response;
        if (editingCaisseOperationId) {
            // Modification d'une opération existante
            console.log('🔄 Mode MODIFICATION - ID:', editingCaisseOperationId);
            console.log('📦 Données envoyées:', operationData);
            
            response = await apiCall(`/transactions.php?id=${editingCaisseOperationId}`, {
                method: 'PUT',
                body: operationData
            });
        } else {
            // Création d'une nouvelle opération
            console.log('➕ Mode CRÉATION');
            console.log('📦 Données envoyées:', operationData);
            
            response = await apiCall('/transactions.php', {
                method: 'POST',
                body: operationData
            });
        }
        
        // Afficher une notification de succès
        const caisseDisplay = getCaisseDisplayName(operationData.account_id);
        const typeDisplay = getTypeDisplayName(operationData.type);
        const action = editingCaisseOperationId ? 'modifiée' : 'ajoutée';
        showNotification(
            `✅ Opération ${typeDisplay.toLowerCase()} de ${operationData.amount.toFixed(2)} € ${action} à ${caisseDisplay}`, 
            'success'
        );
        
        // Recharger toutes les données depuis la base de données
        await loadAllData();
        await updateCaisseHistoriqueDisplay();
        
        if (continueAdding && !editingCaisseOperationId) {
            // Réinitialiser le formulaire pour une nouvelle saisie (seulement en mode ajout)
            document.getElementById('caisseOperationForm').reset();
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('caisse-operation-date').value = today;
            document.getElementById('operation-summary').style.display = 'none';
            editingCaisseOperationId = null;
        } else {
            // Fermer le modal
            closeCaisseOperationModal();
        }
        
    } catch (error) {
        console.error('Erreur lors de la sauvegarde:', error);
        showNotification(error.message || 'Erreur lors de la sauvegarde de l\'opération.', 'error');
    }
}


/**
 * Retourne le nom d'affichage d'un type d'opération
 */
function getTypeDisplayName(type) {
    const types = {
        // Types API standards (compatibles)
        'recette': 'Recette',
        'depense': 'Dépense',
        // Types anciens pour rétrocompatibilité
        'encaissement': 'Encaissement',
        'decaissement': 'Décaissement',
        'virement_in': 'Virement entrant',
        'virement_out': 'Virement sortant',
        'virement_debit': 'Virement (débit)',
        'virement_credit': 'Virement (crédit)'
    };
    return types[type] || 'Type Inconnu';
}

// Rendre les nouvelles fonctions disponibles globalement
/**
 * Ouvre le modal de transfert entre caisses
 */
function openCaisseTransferModal() {
    console.log('💰 Ouverture modal transfert universel');
    
    // S'assurer que les comptes sont à jour
    updateAccountSelects();
    
    // Utiliser la modale de transfert existante
    document.getElementById('transferModal').style.display = 'block';
    
    // Adapter le titre pour transfert universel
    const modalTitle = document.querySelector('#transferModal h3');
    if (modalTitle) {
        modalTitle.innerHTML = '<i class="fas fa-exchange-alt mr-2 text-purple-600"></i>Transfert entre Comptes';
    }
    
    // Les comptes sont déjà peuplés par updateAccountSelects() avec TOUS les comptes
    // Plus besoin de les limiter aux caisses seulement
    
    // Définir la date actuelle par défaut
    const dateField = document.getElementById('transfer-date');
    if (dateField) {
        const today = new Date().toISOString().split('T')[0];
        dateField.value = today;
    }
    
    // Mettre une description par défaut pour transfert universel
    const descField = document.getElementById('transfer-description');
    if (descField) {
        descField.placeholder = 'Ex: Transfert caisse vers banque, alimentation caisse...';
        descField.value = 'Transfert de fonds';
    }
}

window.showBanqueTab = showBanqueTab;
window.showCaisseTab = showCaisseTab;
window.loadBanqueHistorique = loadBanqueHistorique;
window.loadCaisseHistorique = loadCaisseHistorique;
window.initializeSectionTabs = initializeSectionTabs;
window.openCaisseOperationModal = openCaisseOperationModal;
window.closeCaisseOperationModal = closeCaisseOperationModal;
window.saveCaisseOperation = saveCaisseOperation;
window.openCaisseTransferModal = openCaisseTransferModal;
window.editCaisseOperation = editCaisseOperation;
window.deleteCaisseOperation = deleteCaisseOperation;
window.applyCaisseHistoriqueFilters = applyCaisseHistoriqueFilters;
window.debouncedApplyCaisseHistoriqueFilters = debouncedApplyCaisseHistoriqueFilters;
window.updateCaisseHistoriqueDisplay = updateCaisseHistoriqueDisplay;

// Initialiser les onglets au chargement du DOM
document.addEventListener('DOMContentLoaded', function() {
    initializeSectionTabs();
});

console.log('🎯 Fonctions onglets Banque/Caisse chargées');
console.log('💰 Fonctions opérations de caisse chargées');

// ========== SYSTÈME DE FILTRES INTELLIGENTS PROFESSIONNELS ==========

/**
 * Gestion des filtres avec icônes cliquables et recherche globale
 * Système avancé pour tous les onglets de la section Caisse
 */

// État global des filtres pour chaque section
const filterStates = {
    caisses: {},
    historique: {},
    mouvements: {}
};

// Configuration des filtres pour chaque section
const filterConfigs = {
    caisses: {
        searchPlaceholder: "Rechercher dans toutes les données des caisses...",
        searchFields: ['nom', 'reference', 'type', 'statut', 'responsable', 'solde']
    },
    historique: {
        searchPlaceholder: "Rechercher par description, référence, montant, utilisateur...",
        searchFields: ['description', 'reference', 'montant', 'type', 'caisse', 'operateur', 'date']
    },
    mouvements: {
        searchPlaceholder: "Rechercher par description, référence, montant, caisses...",
        searchFields: ['description', 'reference', 'montant', 'type', 'caisse_source', 'caisse_destination']
    }
};

/**
 * Toggle d'un dropdown de filtre avec animation
 * @param {string} filterId - ID du filtre à afficher/masquer
 */
function toggleFilterDropdown(filterId) {
    const dropdown = document.getElementById(`${filterId}Dropdown`);
    const button = document.getElementById(`${filterId}Btn`);
    
    if (!dropdown || !button) return;
    
    // Fermer tous les autres dropdowns
    closeAllFilterDropdowns(filterId);
    
    // Toggle le dropdown actuel
    const isActive = dropdown.classList.contains('active');
    
    if (isActive) {
        // Fermer
        dropdown.classList.remove('active');
        button.classList.remove('active');
    } else {
        // Ouvrir avec animation
        dropdown.classList.add('active');
        button.classList.add('active');
        
        // Ajouter effet de pulse au bouton
        button.classList.add('pulse');
        setTimeout(() => button.classList.remove('pulse'), 300);
    }
}

/**
 * Ferme tous les dropdowns de filtres sauf celui spécifié
 * @param {string} exceptId - ID du filtre à ne pas fermer
 */
function closeAllFilterDropdowns(exceptId = null) {
    const dropdowns = document.querySelectorAll('.filter-dropdown');
    const buttons = document.querySelectorAll('.filter-icon-btn');
    
    dropdowns.forEach(dropdown => {
        if (!exceptId || !dropdown.id.includes(exceptId)) {
            dropdown.classList.remove('active');
        }
    });
    
    buttons.forEach(button => {
        if (!exceptId || !button.id.includes(exceptId)) {
            button.classList.remove('active');
        }
    });
}

/**
 * Toggle d'une option de filtre avec mise à jour de l'état
 * @param {HTMLElement} element - Élément cliqué
 * @param {string} filterType - Type de filtre
 * @param {string} value - Valeur de l'option
 */
function toggleFilterOption(element, filterType, value) {
    const isSelected = element.classList.contains('selected');
    
    // Déterminer la section actuelle
    const section = getCurrentSection();
    
    if (!filterStates[section]) {
        filterStates[section] = {};
    }
    
    if (!filterStates[section][filterType]) {
        filterStates[section][filterType] = [];
    }
    
    if (isSelected) {
        // Désélectionner
        element.classList.remove('selected');
        const index = filterStates[section][filterType].indexOf(value);
        if (index > -1) {
            filterStates[section][filterType].splice(index, 1);
        }
    } else {
        // Sélectionner
        element.classList.add('selected');
        filterStates[section][filterType].push(value);
    }
    
    // Mettre à jour les compteurs et l'affichage
    updateFilterBadges(section);
    updateActiveFiltersBar(section);
    
    // Appliquer les filtres
    applyFilters(section);
    
    // Animation de feedback
    element.style.transform = 'scale(0.95)';
    setTimeout(() => {
        element.style.transform = 'scale(1)';
    }, 150);
}

/**
 * Détermine la section actuelle basée sur l'onglet actif
 */
function getCurrentSection() {
    const tabs = document.querySelectorAll('.caisse-tab');
    for (let tab of tabs) {
        if (tab.classList.contains('active')) {
            const text = tab.textContent.toLowerCase();
            if (text.includes('caisses')) return 'caisses';
            if (text.includes('historique')) return 'historique';
            if (text.includes('mouvements')) return 'mouvements';
        }
    }
    return 'caisses'; // Par défaut
}

/**
 * Met à jour les badges de comptage sur les icônes de filtres
 * @param {string} section - Section active
 */
function updateFilterBadges(section) {
    const state = filterStates[section] || {};
    
    Object.keys(state).forEach(filterType => {
        const count = state[filterType].length;
        const badge = document.getElementById(`${filterType}FilterCount`);
        
        if (badge) {
            if (count > 0) {
                badge.textContent = count;
                badge.style.display = 'flex';
                
                // Animation d'apparition
                badge.style.transform = 'scale(0)';
                setTimeout(() => {
                    badge.style.transform = 'scale(1)';
                }, 50);
            } else {
                badge.style.display = 'none';
            }
        }
    });
}

/**
 * Met à jour la barre des filtres actifs
 * @param {string} section - Section active
 */
function updateActiveFiltersBar(section) {
    const barId = section === 'historique' ? 'activeHistoriqueFiltersBar' : 
                  section === 'mouvements' ? 'activeMouvementsFiltersBar' : 'activeFiltersBar';
    const tagsId = section === 'historique' ? 'activeHistoriqueFilterTags' : 
                   section === 'mouvements' ? 'activeMouvementsFilterTags' : 'activeFilterTags';
    
    const bar = document.getElementById(barId);
    const tagsContainer = document.getElementById(tagsId);
    
    if (!bar || !tagsContainer) return;
    
    const state = filterStates[section] || {};
    const hasActiveFilters = Object.values(state).some(arr => arr.length > 0);
    
    if (hasActiveFilters) {
        bar.style.display = 'flex';
        
        // Générer les tags
        let tagsHTML = '';
        Object.entries(state).forEach(([filterType, values]) => {
            values.forEach(value => {
                tagsHTML += `
                    <div class="active-filter-tag">
                        <span>${getFilterLabel(filterType, value)}</span>
                        <button onclick="removeFilterTag('${section}', '${filterType}', '${value}')">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
            });
        });
        
        tagsContainer.innerHTML = tagsHTML;
        
        // Animation d'apparition
        bar.style.opacity = '0';
        bar.style.transform = 'translateY(-10px)';
        setTimeout(() => {
            bar.style.opacity = '1';
            bar.style.transform = 'translateY(0)';
        }, 100);
        
    } else {
        bar.style.display = 'none';
    }
}

/**
 * Obtient le label d'un filtre pour l'affichage
 * @param {string} filterType - Type de filtre
 * @param {string} value - Valeur du filtre
 */
function getFilterLabel(filterType, value) {
    const labels = {
        type: { principale: 'Principale', secondaire: 'Secondaire', petite: 'Petite Caisse', mobile: 'Mobile' },
        status: { ouverte: 'Ouverte', fermee: 'Fermée', maintenance: 'Maintenance', suspendue: 'Suspendue' },
        amount: { low: '< €500', medium: '€500-€2000', high: '> €2000' },
        activity: { high: 'Très Active', medium: 'Modérée', low: 'Faible' },
        operation: { 
            // Types API (actuels)
            recette: 'Recette', 
            depense: 'Dépense',
            // Types anciens (rétrocompatibilité) 
            encaissement: 'Encaissement', 
            decaissement: 'Décaissement', 
            virement_in: 'Virement entrant', 
            virement_out: 'Virement sortant' 
        },
        user: { marie: 'Marie D.', jean: 'Jean L.', admin: 'Admin', system: 'Système' }
    };
    
    return labels[filterType]?.[value] || value.charAt(0).toUpperCase() + value.slice(1);
}

/**
 * Supprime un tag de filtre spécifique
 * @param {string} section - Section
 * @param {string} filterType - Type de filtre
 * @param {string} value - Valeur à supprimer
 */
function removeFilterTag(section, filterType, value) {
    if (filterStates[section] && filterStates[section][filterType]) {
        const index = filterStates[section][filterType].indexOf(value);
        if (index > -1) {
            filterStates[section][filterType].splice(index, 1);
        }
    }
    
    // Mettre à jour l'option visuelle
    const options = document.querySelectorAll('.filter-option');
    options.forEach(option => {
        if (option.getAttribute('onclick')?.includes(`'${filterType}'`) && 
            option.getAttribute('onclick')?.includes(`'${value}'`)) {
            option.classList.remove('selected');
        }
    });
    
    updateFilterBadges(section);
    updateActiveFiltersBar(section);
    applyFilters(section);
}

/**
 * Efface tous les filtres d'une section
 * @param {string} section - Section à nettoyer
 */
function clearAllFilters(section) {
    // Fonction générique pour toutes les sections
    if (section === 'historique') {
        clearAllHistoriqueFilters();
    } else if (section === 'mouvements') {
        clearAllMouvementsFilters();
    } else {
        clearAllCaissesFilters();
    }
}

function clearAllCaissesFilters() {
    filterStates.caisses = {};
    document.querySelectorAll('#caisse-caisses-content .filter-option.selected').forEach(el => {
        el.classList.remove('selected');
    });
    updateFilterBadges('caisses');
    updateActiveFiltersBar('caisses');
    applyFilters('caisses');
}

function clearAllHistoriqueFilters() {
    filterStates.historique = {};
    document.querySelectorAll('#caisse-historique-content .filter-option.selected').forEach(el => {
        el.classList.remove('selected');
    });
    updateFilterBadges('historique');
    updateActiveFiltersBar('historique');
    applyFilters('historique');
}

function clearAllMouvementsFilters() {
    filterStates.mouvements = {};
    document.querySelectorAll('#caisse-mouvements-content .filter-option.selected').forEach(el => {
        el.classList.remove('selected');
    });
    updateFilterBadges('mouvements');
    updateActiveFiltersBar('mouvements');
    applyFilters('mouvements');
}

/**
 * Applique les filtres aux données affichées
 * @param {string} section - Section à filtrer
 */
function applyFilters(section) {
    // Cette fonction filtrerait les données réelles
    // Pour la démo, on simule l'application des filtres
    console.log(`🔍 Filtres appliqués pour ${section}:`, filterStates[section]);
    
    // Animation de feedback
    const container = document.querySelector(`#caisse-${section}-content`);
    if (container) {
        container.style.opacity = '0.7';
        setTimeout(() => {
            container.style.opacity = '1';
        }, 200);
    }
    
    // Simuler un délai de filtrage
    showFilteringAnimation(section);
}

/**
 * Affiche une animation de filtrage
 * @param {string} section - Section en cours de filtrage
 */
function showFilteringAnimation(section) {
    const searchInput = document.getElementById(`global${section.charAt(0).toUpperCase() + section.slice(1)}Search`);
    if (searchInput) {
        const icon = searchInput.nextElementSibling;
        if (icon) {
            icon.classList.add('fa-spin');
            setTimeout(() => {
                icon.classList.remove('fa-spin');
            }, 500);
        }
    }
}

/**
 * Recherche globale dans tous les champs
 * @param {string} query - Terme de recherche
 */
function performGlobalCaisseSearch(query) {
    console.log('🔍 Recherche Caisses:', query);
    showFilteringAnimation('caisses');
    // Ici on filtrerait les cartes de caisses
}

function performGlobalHistoriqueSearch(query) {
    console.log('🔍 Recherche Historique:', query);
    showFilteringAnimation('historique');
    // Ici on filtrerait le tableau d'historique
}

function performGlobalMouvementsSearch(query) {
    console.log('🔍 Recherche Mouvements:', query);
    showFilteringAnimation('mouvements');
    // Ici on filtrerait les mouvements
}

/**
 * Initialise les événements des filtres
 */
function initializeAdvancedFilters() {
    // Fermer les dropdowns quand on clique ailleurs
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.filter-icons-container')) {
            closeAllFilterDropdowns();
        }
    });
    
    // Initialiser les zones de recherche
    const searchInputs = document.querySelectorAll('.global-search-input');
    searchInputs.forEach(input => {
        input.addEventListener('input', function() {
            const section = this.id.includes('Caisse') ? 'caisses' :
                           this.id.includes('Historique') ? 'historique' :
                           this.id.includes('Mouvements') ? 'mouvements' : 'caisses';
            
            // Ajouter un délai pour éviter trop d'appels
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                if (section === 'caisses') performGlobalCaisseSearch(this.value);
                else if (section === 'historique') performGlobalHistoriqueSearch(this.value);
                else if (section === 'mouvements') performGlobalMouvementsSearch(this.value);
            }, 300);
        });
    });
    
    console.log('✨ Système de filtres intelligents initialisé');
}

/**
 * Gestion des boutons de pagination améliorée
 */
function initializePaginationControls() {
    // Gestion du sélecteur "Aller à la page"
    const pageInputs = document.querySelectorAll('input[type="number"][max]');
    pageInputs.forEach(input => {
        const button = input.nextElementSibling;
        if (button) {
            button.addEventListener('click', function() {
                const page = parseInt(input.value);
                const max = parseInt(input.getAttribute('max'));
                
                if (page >= 1 && page <= max) {
                    goToPage(page);
                } else {
                    showNotification(`Page invalide. Entrez un numéro entre 1 et ${max}.`, 'warning');
                }
            });
        }
    });
}

/**
 * Navigation vers une page spécifique
 * @param {number} page - Numéro de page
 */
function goToPage(page) {
    console.log(`📄 Navigation vers la page ${page}`);
    showNotification(`Navigation vers la page ${page}`, 'info');
    
    // Animation de transition
    const tableContainer = document.querySelector('.table-modern');
    if (tableContainer) {
        tableContainer.style.opacity = '0.5';
        setTimeout(() => {
            tableContainer.style.opacity = '1';
        }, 300);
    }
}

/**
 * Gestion de la sélection multiple dans les tableaux
 */
function initializeTableSelection() {
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectionStats();
        });
    }
    
    // Écouter les changements sur les checkboxes individuelles
    document.addEventListener('change', function(event) {
        if (event.target.type === 'checkbox' && event.target.closest('tbody')) {
            updateSelectionStats();
        }
    });
}

/**
 * Met à jour les statistiques de sélection
 */
function updateSelectionStats() {
    const selectedCheckboxes = document.querySelectorAll('tbody input[type="checkbox"]:checked');
    const statsElement = document.getElementById('selectionStats');
    
    if (selectedCheckboxes.length > 0) {
        if (statsElement) {
            statsElement.style.display = 'block';
            statsElement.innerHTML = `
                <span class="font-medium">${selectedCheckboxes.length} opérations sélectionnées</span>
                <span class="mx-2">•</span>
                <span>Total: <span class="font-bold text-orange-600">€${calculateSelectionTotal()}</span></span>
                <button class="ml-3 text-blue-600 hover:text-blue-800 font-medium">Actions en lot</button>
            `;
        }
    } else {
        if (statsElement) {
            statsElement.style.display = 'none';
        }
    }
}

/**
 * Calcule le total des éléments sélectionnés
 */
function calculateSelectionTotal() {
    // Simuler un calcul de total
    const selectedCount = document.querySelectorAll('tbody input[type="checkbox"]:checked').length;
    return (selectedCount * 123.45).toFixed(2);
}

/**
 * Initialise tous les systèmes de l'interface moderne
 */
function initializeModernInterface() {
    initializeAdvancedFilters();
    initializePaginationControls();
    initializeTableSelection();
    
    // Animation d'initialisation
    const containers = document.querySelectorAll('.advanced-filters-container');
    containers.forEach((container, index) => {
        container.style.opacity = '0';
        container.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            container.style.transition = 'all 0.6s ease-out';
            container.style.opacity = '1';
            container.style.transform = 'translateY(0)';
        }, index * 200);
    });
    
    console.log('🎨 Interface moderne complètement initialisée');
}

// Rendre les fonctions disponibles globalement
window.toggleFilterDropdown = toggleFilterDropdown;
window.toggleFilterOption = toggleFilterOption;
window.removeFilterTag = removeFilterTag;
window.clearAllFilters = clearAllFilters;
window.clearAllCaissesFilters = clearAllCaissesFilters;
window.clearAllHistoriqueFilters = clearAllHistoriqueFilters;
window.clearAllMouvementsFilters = clearAllMouvementsFilters;
window.performGlobalCaisseSearch = performGlobalCaisseSearch;
window.performGlobalHistoriqueSearch = performGlobalHistoriqueSearch;
window.performGlobalMouvementsSearch = performGlobalMouvementsSearch;
window.goToPage = goToPage;

// Initialiser l'interface moderne au chargement
document.addEventListener('DOMContentLoaded', function() {
    // Délai pour s'assurer que tout est chargé
    setTimeout(initializeModernInterface, 500);
});

console.log('🚀 Système de filtres intelligents professionnels chargé');
console.log('✨ Interface moderne et interactive prête');
// ========================================
// GESTION DU MODAL TRANSACTION AVEC ONGLETS
// ========================================

/**
 * Change l'onglet actif dans le modal de transaction
 */
function switchTransactionTab(tabName) {
    // Masquer tous les contenus d'onglets
    const allTabContents = document.querySelectorAll('.transaction-tab-content');
    allTabContents.forEach(content => {
        content.style.display = 'none';
    });

    // Désactiver tous les boutons d'onglets
    const allTabs = document.querySelectorAll('.transaction-tab');
    allTabs.forEach(tab => {
        tab.classList.remove('border-blue-500', 'text-blue-600', 'font-semibold');
        tab.classList.add('border-transparent', 'text-gray-500', 'font-medium');
    });

    // Activer l'onglet sélectionné
    const activeTab = document.getElementById(`tab-${tabName}`);
    if (activeTab) {
        activeTab.classList.remove('border-transparent', 'text-gray-500', 'font-medium');
        activeTab.classList.add('border-blue-500', 'text-blue-600', 'font-semibold');
    }

    // Afficher le contenu de l'onglet sélectionné
    const activeContent = document.getElementById(`content-${tabName}`);
    if (activeContent) {
        activeContent.style.display = 'block';
    }
}

/**
 * Met à jour le récapitulatif de la transaction en temps réel
 */
function updateTransactionSummary() {
    // Récupérer les valeurs des champs
    const type = document.getElementById('transaction-type')?.value || '-';
    const accountSelect = document.getElementById('transaction-account');
    const account = accountSelect?.options[accountSelect.selectedIndex]?.text || '-';
    const amount = parseFloat(document.getElementById('transaction-amount')?.value) || 0;
    const date = document.getElementById('transaction-date')?.value || '-';
    const reference = document.getElementById('transaction-reference')?.value || '-';
    const paymentMethodSelect = document.getElementById('transaction-payment-method');
    const paymentMethod = paymentMethodSelect?.options[paymentMethodSelect.selectedIndex]?.text || '-';
    const categorySelect = document.getElementById('transaction-category');
    const category = categorySelect?.options[categorySelect.selectedIndex]?.text || '-';
    const bankStatusSelect = document.getElementById('transaction-bank-status');
    const bankStatus = bankStatusSelect?.options[bankStatusSelect.selectedIndex]?.text || '⏳ En attente';
    const valueDate = document.getElementById('transaction-value-date')?.value || '-';

    // Mettre à jour le récapitulatif
    const summaryType = document.getElementById('summary-type');
    if (summaryType) {
        summaryType.textContent = type === 'recette' ? '💰 Recette' : (type === 'depense' ? '💸 Dépense' : '-');
    }

    const summaryAccount = document.getElementById('summary-account');
    if (summaryAccount) summaryAccount.textContent = account;

    const summaryAmount = document.getElementById('summary-amount');
    if (summaryAmount) summaryAmount.textContent = amount.toFixed(2) + ' €';

    const summaryDate = document.getElementById('summary-date');
    if (summaryDate) {
        if (date && date !== '-') {
            const dateObj = new Date(date);
            summaryDate.textContent = dateObj.toLocaleDateString('fr-FR');
        } else {
            summaryDate.textContent = '-';
        }
    }

    const summaryReference = document.getElementById('summary-reference');
    if (summaryReference) summaryReference.textContent = reference;

    const summaryPaymentMethod = document.getElementById('summary-payment-method');
    if (summaryPaymentMethod) summaryPaymentMethod.textContent = paymentMethod;

    const summaryCategory = document.getElementById('summary-category');
    if (summaryCategory) summaryCategory.textContent = category;

    const summaryBankStatus = document.getElementById('summary-bank-status');
    if (summaryBankStatus) summaryBankStatus.textContent = bankStatus;

    const summaryValueDate = document.getElementById('summary-value-date');
    if (summaryValueDate) {
        if (valueDate && valueDate !== '-') {
            const dateObj = new Date(valueDate);
            summaryValueDate.textContent = dateObj.toLocaleDateString('fr-FR');
        } else {
            summaryValueDate.textContent = '-';
        }
    }

    // Calculer l'impact sur le solde
    const impact = type === 'recette' ? amount : -amount;
    const summaryImpact = document.getElementById('summary-impact');
    if (summaryImpact) {
        summaryImpact.textContent = (impact >= 0 ? '+' : '') + impact.toFixed(2) + ' €';
        summaryImpact.style.color = impact >= 0 ? '#10b981' : '#ef4444';
    }

    // Mettre à jour le champ "Impact sur le solde" dans l'onglet bancaire
    const balanceImpact = document.getElementById('transaction-balance-impact');
    if (balanceImpact) {
        balanceImpact.value = (impact >= 0 ? '+' : '') + impact.toFixed(2) + ' €';
        balanceImpact.style.color = impact >= 0 ? '#10b981' : '#ef4444';
    }
}

/**
 * Ouvre le dialogue de sélection de fichier
 */
function triggerFileUpload() {
    const fileInput = document.getElementById('transaction-file-input');
    if (fileInput) {
        fileInput.click();
    }
}

/**
 * Gère les fichiers sélectionnés - VERSION CUMULATIVE
 * Ajoute les nouveaux fichiers à la liste existante au lieu de la remplacer
 */
function handleFileSelection() {
    const fileInput = document.getElementById('transaction-file-input');
    
    if (!fileInput || !fileInput.files || fileInput.files.length === 0) return;
    
    // Ajouter les nouveaux fichiers au tableau existant (cumulative)
    Array.from(fileInput.files).forEach(file => {
        // Vérifier que le fichier n'est pas déjà dans la liste (éviter les doublons)
        const isDuplicate = transactionFiles.some(existingFile => 
            existingFile.name === file.name && 
            existingFile.size === file.size && 
            existingFile.lastModified === file.lastModified
        );
        
        if (!isDuplicate) {
            transactionFiles.push(file);
        }
    });
    
    // Réinitialiser l'input file pour permettre de sélectionner à nouveau les mêmes fichiers si nécessaire
    fileInput.value = '';
    
    // Re-rendre la liste complète
    renderFilesList();
}

/**
 * Affiche tous les fichiers de la liste transactionFiles
 */
function renderFilesList() {
    const documentsList = document.getElementById('documents-list');
    
    if (!documentsList) return;
    
    // Si aucun fichier, afficher le message par défaut
    if (transactionFiles.length === 0) {
        documentsList.innerHTML = '<p class="text-gray-500 text-center py-8 italic">Aucun document joint pour le moment</p>';
        return;
    }
    
    // Vider la liste avant de la re-rendre
    documentsList.innerHTML = '';
    
    // Afficher chaque fichier
    transactionFiles.forEach((file, index) => {
        const fileSize = (file.size / 1024 / 1024).toFixed(2); // Taille en Mo
        const fileExtension = file.name.split('.').pop().toUpperCase();
        
        // Icône selon le type de fichier
        let fileIcon = 'fa-file';
        if (['PDF'].includes(fileExtension)) fileIcon = 'fa-file-pdf';
        else if (['JPG', 'JPEG', 'PNG', 'GIF'].includes(fileExtension)) fileIcon = 'fa-file-image';
        else if (['DOC', 'DOCX'].includes(fileExtension)) fileIcon = 'fa-file-word';
        else if (['XLS', 'XLSX'].includes(fileExtension)) fileIcon = 'fa-file-excel';
        
        const fileItem = document.createElement('div');
        fileItem.className = 'flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg hover:border-blue-400 transition-all';
        fileItem.innerHTML = `
            <div class="flex items-center space-x-3">
                <i class="fas ${fileIcon} text-2xl text-blue-600"></i>
                <div>
                    <p class="font-medium text-gray-800">${sanitizeHTML(file.name)}</p>
                    <p class="text-xs text-gray-500">${sanitizeHTML(fileExtension)} • ${fileSize} Mo</p>
                </div>
            </div>
            <div class="flex space-x-2">
                <button type="button" onclick="previewFile(${index})" class="px-3 py-1 text-blue-600 hover:bg-blue-50 rounded" title="Prévisualiser">
                    <i class="fas fa-eye"></i>
                </button>
                <button type="button" onclick="removeFile(${index})" class="px-3 py-1 text-red-600 hover:bg-red-50 rounded" title="Supprimer">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        documentsList.appendChild(fileItem);
    });
}

/**
 * Affiche les documents existants d'une transaction (déjà enregistrés en base de données)
 */
function renderExistingDocuments(documents) {
    const documentsList = document.getElementById('documents-list');
    
    if (!documentsList) return;
    
    // Si aucun document, afficher le message par défaut
    if (!documents || documents.length === 0) {
        documentsList.innerHTML = '<p class="text-gray-500 text-center py-8 italic">Aucun document joint pour le moment</p>';
        return;
    }
    
    // Vider la liste avant de la re-rendre
    documentsList.innerHTML = '';
    
    // Afficher chaque document existant
    documents.forEach((doc) => {
        // Utiliser original_name si disponible, sinon file_name
        const fileName = doc.original_name || doc.file_name;
        const fileExtension = fileName.split('.').pop().toUpperCase();
        const fileSize = doc.file_size ? (doc.file_size / 1024 / 1024).toFixed(2) : '?';
        
        // Icône selon le type de fichier
        let fileIcon = 'fa-file';
        if (['PDF'].includes(fileExtension)) fileIcon = 'fa-file-pdf';
        else if (['JPG', 'JPEG', 'PNG', 'GIF'].includes(fileExtension)) fileIcon = 'fa-file-image';
        else if (['DOC', 'DOCX'].includes(fileExtension)) fileIcon = 'fa-file-word';
        else if (['XLS', 'XLSX'].includes(fileExtension)) fileIcon = 'fa-file-excel';
        
        const fileItem = document.createElement('div');
        fileItem.className = 'flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg hover:border-blue-400 transition-all';
        fileItem.innerHTML = `
            <div class="flex items-center space-x-3">
                <i class="fas ${fileIcon} text-2xl text-green-600"></i>
                <div>
                    <p class="font-medium text-gray-800">${sanitizeHTML(fileName)}</p>
                    <p class="text-xs text-gray-500">${sanitizeHTML(fileExtension)} • ${fileSize} Mo</p>
                </div>
            </div>
            <div class="flex space-x-2">
                <button type="button" onclick="previewDocument('${doc.id}', '${sanitizeHTML(fileName)}')" class="px-3 py-1 text-blue-600 hover:bg-blue-50 rounded" title="Visualiser">
                    <i class="fas fa-eye"></i>
                </button>
                <button type="button" onclick="downloadDocument('${doc.id}', '${sanitizeHTML(fileName)}')" class="px-3 py-1 text-green-600 hover:bg-green-50 rounded" title="Télécharger">
                    <i class="fas fa-download"></i>
                </button>
                <button type="button" onclick="deleteExistingDocument('${doc.id}')" class="px-3 py-1 text-red-600 hover:bg-red-50 rounded" title="Supprimer">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        documentsList.appendChild(fileItem);
    });
}

/**
 * Télécharge un document existant
 */
async function downloadDocument(documentId, fileName) {
    try {
        const response = await fetch(`${API_BASE}/download_document.php?id=${documentId}`);
        
        if (!response.ok) {
            throw new Error('Erreur lors du téléchargement');
        }
        
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = fileName;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        showNotification('Document téléchargé avec succès', 'success');
    } catch (error) {
        console.error('Erreur lors du téléchargement:', error);
        showNotification('Erreur lors du téléchargement du document', 'error');
    }
}

/**
 * Supprime un document existant de la base de données
 */
async function deleteExistingDocument(documentId) {
    if (!confirm('Voulez-vous vraiment supprimer définitivement ce document ?')) {
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE}/delete_document.php?id=${documentId}`, {
            method: 'DELETE'
        });
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || 'Erreur lors de la suppression');
        }
        
        showNotification('Document supprimé avec succès', 'success');
        
        // Recharger la transaction pour mettre à jour la liste des documents
        if (editingId) {
            await editTransaction(editingId);
        }
    } catch (error) {
        console.error('Erreur lors de la suppression du document:', error);
        showNotification(error.message, 'error');
    }
}

/**
 * Prévisualiser un fichier (placeholder)
 */
/**
 * Visualise un document existant dans un modal ou nouvel onglet
 * @param {string} documentId - ID du document
 * @param {string} fileName - Nom du fichier
 */
async function previewDocument(documentId, fileName) {
    try {
        const response = await fetch(`${API_BASE}/download_document.php?id=${documentId}&mode=preview`);
        
        if (!response.ok) {
            throw new Error('Erreur lors de la récupération du document');
        }
        
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        
        // Déterminer le type de fichier
        const fileExtension = fileName.split('.').pop().toLowerCase();
        const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        const pdfExtensions = ['pdf'];
        
        if (imageExtensions.includes(fileExtension)) {
            // Pour les images, afficher dans un modal
            showImagePreviewModal(url, fileName);
            // Libérer l'URL après un délai
            setTimeout(() => window.URL.revokeObjectURL(url), 5000);
            
        } else if (pdfExtensions.includes(fileExtension)) {
            // Pour les PDF, ouvrir dans un nouvel onglet ou utiliser le viewer du navigateur
            const newWindow = window.open();
            if (newWindow) {
                newWindow.location = url;
                newWindow.onload = () => {
                    setTimeout(() => {
                        if (newWindow.location.href !== 'about:blank') {
                            window.URL.revokeObjectURL(url);
                        }
                    }, 5000);
                };
                showNotification('Document PDF ouvert dans un nouvel onglet', 'success');
            } else {
                throw new Error('Impossible d\'ouvrir un nouvel onglet. Vérifiez les paramètres de blocage de popups.');
            }
            
        } else {
            // Pour les autres types de fichiers, proposer le téléchargement
            const fileSize = (blob.size / 1024 / 1024).toFixed(2);
            const shouldDownload = confirm(`📄 Document: ${fileName}\n📊 Taille: ${fileSize} Mo\n\n💡 Ce type de fichier ne peut pas être visualisé.\n\nVoulez-vous télécharger le document ?`);
            
            if (shouldDownload) {
                // Télécharger le fichier
                const downloadLink = document.createElement('a');
                downloadLink.href = url;
                downloadLink.download = fileName;
                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);
                
                setTimeout(() => window.URL.revokeObjectURL(url), 3000);
                showNotification('Document téléchargé', 'success');
            } else {
                window.URL.revokeObjectURL(url);
            }
        }
        
    } catch (error) {
        console.error('Erreur lors de la visualisation:', error);
        showNotification('Erreur lors de la visualisation du document', 'error');
    }
}

/**
 * Affiche une image dans un modal de prévisualisation
 * @param {string} imageUrl - URL de l'image
 * @param {string} fileName - Nom du fichier
 */
function showImagePreviewModal(imageUrl, fileName) {
    // Créer le modal
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50';
    modal.onclick = (e) => {
        if (e.target === modal) {
            closePreviewModal();
        }
    };
    
    modal.innerHTML = `
        <div class="max-w-7xl max-h-full bg-white rounded-lg shadow-xl overflow-hidden">
            <div class="flex items-center justify-between p-4 bg-gray-100 border-b">
                <h3 class="text-lg font-semibold text-gray-800 truncate">${sanitizeHTML(fileName)}</h3>
                <div class="flex space-x-2">
                    <button onclick="downloadCurrentImage('${imageUrl}', '${sanitizeHTML(fileName)}')" class="px-3 py-1 text-green-600 hover:bg-green-100 rounded transition-colors" title="Télécharger">
                        <i class="fas fa-download"></i> Télécharger
                    </button>
                    <button onclick="closePreviewModal()" class="px-3 py-1 text-gray-600 hover:bg-gray-200 rounded transition-colors" title="Fermer">
                        <i class="fas fa-times"></i> Fermer
                    </button>
                </div>
            </div>
            <div class="flex items-center justify-center p-4 max-h-[80vh] overflow-auto">
                <img src="${imageUrl}" alt="${sanitizeHTML(fileName)}" class="max-w-full max-h-full object-contain rounded" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <div class="hidden text-center p-8">
                    <i class="fas fa-exclamation-triangle text-4xl text-yellow-500 mb-4"></i>
                    <p class="text-gray-600">Impossible de charger l'image</p>
                </div>
            </div>
        </div>
    `;
    
    // Ajouter le modal au body
    document.body.appendChild(modal);
    
    // Stocker la référence pour pouvoir la fermer
    window.currentPreviewModal = modal;
    
    // Éviter le défilement de la page de fond
    document.body.style.overflow = 'hidden';
}

/**
 * Ferme le modal de prévisualisation
 */
function closePreviewModal() {
    if (window.currentPreviewModal) {
        document.body.removeChild(window.currentPreviewModal);
        window.currentPreviewModal = null;
        document.body.style.overflow = ''; // Restaurer le défilement
    }
}

/**
 * Télécharge l'image actuellement affichée dans le modal
 * @param {string} imageUrl - URL de l'image
 * @param {string} fileName - Nom du fichier
 */
function downloadCurrentImage(imageUrl, fileName) {
    const link = document.createElement('a');
    link.href = imageUrl;
    link.download = fileName;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/**
 * Affiche tous les détails d'une transaction ou virement dans un modal professionnel
 * @param {Object} transaction - Données de la transaction
 */
function viewTransactionDetails(transaction) {
    // Créer le modal
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto';
    modal.onclick = (e) => {
        if (e.target === modal) {
            closeTransactionDetailsModal();
        }
    };

    // Déterminer le type d'opération et l'icône
    const isTransfer = transaction.type === 'virement_debit' || transaction.type === 'virement_credit';
    const operationIcon = isTransfer ? 'fas fa-exchange-alt' : 'fas fa-receipt';
    const operationTitle = isTransfer ? 'Détails du Virement' : 'Détails de la Transaction';

    // Classes CSS pour les montants selon le type
    const amountClass = getAmountClass(transaction.type);
    const amountFormatted = formatCurrencyForDisplay(transaction);

    // Construire le contenu HTML détaillé
    modal.innerHTML = `
        <div class="bg-white rounded-lg shadow-2xl max-w-4xl w-full mx-4 my-8 max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6 rounded-t-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <i class="${operationIcon} text-2xl"></i>
                        <div>
                            <h2 class="text-2xl font-bold">${operationTitle}</h2>
                            <p class="text-blue-100">Référence: #${transaction.id}</p>
                        </div>
                    </div>
                    <button onclick="closeTransactionDetailsModal()" class="text-white hover:text-gray-200 transition-colors">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
            </div>

            <!-- Body -->
            <div class="p-6 space-y-8">
                <!-- Section Informations Générales -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        Informations Générales
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="bg-white p-4 rounded-lg border">
                            <label class="text-sm font-medium text-gray-600">Date</label>
                            <p class="text-lg font-semibold text-gray-800">${formatDate(transaction.date)}</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg border">
                            <label class="text-sm font-medium text-gray-600">Type</label>
                            <span class="inline-block px-3 py-1 rounded-full text-sm font-medium ${getTypeClass(transaction.type)}">
                                ${getTypeLabel(transaction.type)}
                            </span>
                        </div>
                        <div class="bg-white p-4 rounded-lg border">
                            <label class="text-sm font-medium text-gray-600">Statut</label>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i> Confirmé
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Section Description -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-align-left text-blue-600 mr-2"></i>
                        Description
                    </h3>
                    <div class="bg-white p-4 rounded-lg border">
                        <p class="text-gray-800 text-lg">${sanitizeHTML(transaction.description || 'Aucune description')}</p>
                    </div>
                </div>

                <!-- Section Comptes et Tiers -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Compte -->
                    <div class="bg-blue-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-university text-blue-600 mr-2"></i>
                            Compte
                        </h3>
                        <div class="bg-white p-4 rounded-lg border">
                            <label class="text-sm font-medium text-gray-600">Nom du Compte</label>
                            <p class="text-lg font-semibold text-gray-800">${sanitizeHTML(transaction.account_name || 'N/A')}</p>
                            <p class="text-sm text-gray-500">ID: ${transaction.account_id || 'N/A'}</p>
                        </div>
                    </div>

                    <!-- Tiers -->
                    <div class="bg-green-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-users text-green-600 mr-2"></i>
                            Tiers
                        </h3>
                        <div class="bg-white p-4 rounded-lg border">
                            <label class="text-sm font-medium text-gray-600">Nom du Tiers</label>
                            <p class="text-lg font-semibold text-gray-800">${sanitizeHTML(transaction.tiers_name || 'Aucun')}</p>
                            <p class="text-sm text-gray-500">ID: ${transaction.tiers_id || 'N/A'}</p>
                        </div>
                    </div>
                </div>

                <!-- Section Catégorie -->
                ${transaction.category_name ? `
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-tags text-purple-600 mr-2"></i>
                        Catégorie
                    </h3>
                    <div class="bg-white p-4 rounded-lg border">
                        <p class="text-lg font-semibold text-gray-800">${sanitizeHTML(transaction.category_name)}</p>
                        <p class="text-sm text-gray-500">ID: ${transaction.category_id}</p>
                    </div>
                </div>
                ` : ''}

                <!-- Section Montant et Balance -->
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-calculator text-indigo-600 mr-2"></i>
                        Montant et Balance
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="bg-white p-4 rounded-lg border text-center">
                            <label class="text-sm font-medium text-gray-600 block">Montant</label>
                            <p class="text-2xl font-bold ${amountClass}">${amountFormatted}</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg border text-center">
                            <label class="text-sm font-medium text-gray-600 block">Balance Avant</label>
                            <p class="text-xl font-semibold text-gray-800">${sanitizeHTML(formatCurrency(transaction.balance_before || 0))}</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg border text-center">
                            <label class="text-sm font-medium text-gray-600 block">Balance Après</label>
                            <p class="text-xl font-semibold text-gray-800">${sanitizeHTML(formatCurrency(transaction.balance_after || 0))}</p>
                        </div>
                    </div>
                </div>

                <!-- Section Virement (si applicable) -->
                ${isTransfer ? `
                <div class="bg-yellow-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-link text-yellow-600 mr-2"></i>
                        Informations de Virement
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-white p-4 rounded-lg border">
                            <label class="text-sm font-medium text-gray-600">Référence Transfert</label>
                            <p class="text-lg font-mono text-gray-800">${sanitizeHTML(transaction.transfer_ref || 'N/A')}</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg border">
                            <label class="text-sm font-medium text-gray-600">Type de Virement</label>
                            <span class="inline-block px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                ${transaction.type === 'virement_debit' ? 'Débit (Sortant)' : 'Crédit (Entrant)'}
                            </span>
                        </div>
                    </div>
                </div>
                ` : ''}

                <!-- Section Métadonnées -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-clock text-gray-600 mr-2"></i>
                        Métadonnées
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-white p-4 rounded-lg border">
                            <label class="text-sm font-medium text-gray-600">Date de Création</label>
                            <p class="text-gray-800">${sanitizeHTML(formatDate(transaction.created_at || transaction.date))}</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg border">
                            <label class="text-sm font-medium text-gray-600">Dernière Modification</label>
                            <p class="text-gray-800">${sanitizeHTML(formatDate(transaction.updated_at || transaction.date))}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer avec actions -->
            <div class="bg-gray-50 px-6 py-4 rounded-b-lg border-t">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-info-circle mr-1"></i>
                        Les détails complets de l'opération sont affichés ci-dessus
                    </div>
                    <div class="space-x-3">
                        <button onclick="editTransaction('${transaction.id}')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                            <i class="fas fa-edit mr-2"></i>Modifier
                        </button>
                        <button onclick="closeTransactionDetailsModal()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-times mr-2"></i>Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Ajouter le modal à la page
    document.body.appendChild(modal);
    
    // Stocker la référence pour la fermeture
    window.currentTransactionDetailsModal = modal;
    
    // Empêcher le défilement de la page
    document.body.style.overflow = 'hidden';
}

/**
 * Ferme le modal de détails de transaction
 */
function closeTransactionDetailsModal() {
    if (window.currentTransactionDetailsModal) {
        document.body.removeChild(window.currentTransactionDetailsModal);
        window.currentTransactionDetailsModal = null;
        document.body.style.overflow = '';
    }
}

// Ajouter la fonction closePreviewModal à l'objet window pour qu'elle soit accessible depuis le modal
window.closePreviewModal = closePreviewModal;

/**
 * Supprimer un fichier spécifique de la liste - VERSION CUMULATIVE
 * @param {number} index - L'index du fichier à supprimer dans transactionFiles
 */
function removeFile(index) {
    if (confirm('Voulez-vous vraiment supprimer ce document ?')) {
        // Supprimer le fichier du tableau à l'index spécifié
        transactionFiles.splice(index, 1);
        
        // Re-rendre la liste mise à jour
        renderFilesList();
    }
}

/**
 * Dupliquer la transaction actuelle
 */
function duplicateTransaction() {
    if (confirm('Voulez-vous dupliquer cette transaction ?\n\nUne copie sera créée avec les mêmes données.')) {
        alert('Fonctionnalité de duplication en cours de développement.\n\nCette action créera une copie exacte de la transaction actuelle.');
    }
}

/**
 * Programmer une récurrence
 */
function scheduleRecurrence() {
    alert('Fonctionnalité de récurrence en cours de développement.\n\nVous pourrez programmer cette transaction pour qu\'elle se répète automatiquement (quotidien, hebdomadaire, mensuel, etc.).');
}

// Suppression de la fonction vide - la fonction correcte deleteTransaction(id) est définie plus haut

/**
 * Initialiser le modal de transaction au chargement
 */
function initTransactionModal() {
    // Définir la date par défaut à aujourd'hui
    const dateInput = document.getElementById('transaction-date');
    if (dateInput && !dateInput.value) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.value = today;
    }
    
    // Ajouter des écouteurs pour la mise à jour en temps réel
    const fieldsToWatch = [
        'transaction-type',
        'transaction-account',
        'transaction-amount',
        'transaction-date',
        'transaction-category',
        'transaction-tiers',
        'transaction-reference',
        'transaction-bank-status',
        'transaction-value-date'
    ];
    
    fieldsToWatch.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('change', updateTransactionSummary);
            field.addEventListener('input', updateTransactionSummary);
        }
    });
    
    // Initialiser le récapitulatif
    updateTransactionSummary();
}

/**
 * Fonctions pour la section Achats
 */

/**
 * Charge les données de la vue d'ensemble des achats
 */
async function loadAchatsVueEnsemble() {
    console.log('🛒 Chargement vue d\'ensemble des achats...');
    
    // Simuler le chargement des données (à remplacer par API réelle)
    await new Promise(resolve => setTimeout(resolve, 500));
    
    // Initialiser les graphiques si nécessaire
    if (typeof initAchatsCharts === 'function') {
        initAchatsCharts();
    }
    
    console.log('✅ Vue d\'ensemble des achats chargée');
}

/**
 * Charge les données des enregistrements d'achats
 */
async function loadAchatsEnregistrements() {
    console.log('🛒 Chargement enregistrements d\'achats...');
    
    const tbody = document.getElementById('achats-table');
    if (!tbody) return;
    
    // Afficher le loading
    tbody.innerHTML = `
        <tr>
            <td colspan="10" class="text-center py-8">
                <div class="loading mx-auto"></div>
                <p class="text-gray-500 mt-3">Chargement des achats...</p>
            </td>
        </tr>
    `;
    
    try {
        // Simuler un délai de chargement (remplacer par vraie API)
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        // Données d'exemple (remplacer par vraies données)
        const sampleData = [
            {
                date: '25/10/2025',
                fournisseur: 'Fournitures Pro SARL',
                description: 'Achat matériel bureau',
                categorie: 'Fournitures',
                mode_paiement: 'Virement',
                compte: 'Compte Principal',
                montant: '456.78',
                statut: 'paye'
            },
            {
                date: '24/10/2025',
                fournisseur: 'EDF Business',
                description: 'Facture électricité - Septembre',
                categorie: 'Énergie',
                mode_paiement: 'Prélèvement',
                compte: 'Compte Principal',
                montant: '234.56',
                statut: 'en_attente'
            }
        ];
        
        // Afficher les données
        tbody.innerHTML = sampleData.map((achat, index) => `
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">
                    <input type="checkbox" class="rounded border-gray-300">
                </td>
                <td class="px-4 py-3 text-gray-600">${achat.date}</td>
                <td class="px-4 py-3 font-medium">${achat.fournisseur}</td>
                <td class="px-4 py-3">${achat.description}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">${achat.categorie}</span>
                </td>
                <td class="px-4 py-3 text-gray-600">${achat.mode_paiement}</td>
                <td class="px-4 py-3 text-gray-600">${achat.compte}</td>
                <td class="px-4 py-3 text-right font-semibold">€${achat.montant}</td>
                <td class="px-4 py-3 text-center">
                    <span class="px-2 py-1 ${achat.statut === 'paye' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'} rounded-full text-xs">
                        ${achat.statut === 'paye' ? 'Payé' : 'En attente'}
                    </span>
                </td>
                <td class="px-4 py-3 text-center no-print">
                    <button onclick="viewPurchaseDetails('${index}')" class="text-blue-600 hover:text-blue-800 mr-2" title="Voir">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button onclick="editPurchase('${index}')" class="text-green-600 hover:text-green-800 mr-2" title="Modifier">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deletePurchase('${index}')" class="text-red-600 hover:text-red-800" title="Supprimer">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
        
    } catch (error) {
        console.error('Erreur lors du chargement des achats:', error);
        tbody.innerHTML = `
            <tr>
                <td colspan="10" class="text-center py-8 text-red-500">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Erreur lors du chargement des achats
                </td>
            </tr>
        `;
    }
    
    console.log('✅ Enregistrements d\'achats chargés');
}

/**
 * Charge les données du suivi des paiements
 */
async function loadAchatsSuiviPaiements() {
    console.log('🛒 Chargement suivi des paiements...');
    
    // Simuler le chargement des données (à remplacer par API réelle)
    await new Promise(resolve => setTimeout(resolve, 500));
    
    console.log('✅ Suivi des paiements chargé');
}

/**
 * Initialise les graphiques des achats
 */
function initAchatsCharts() {
    // Graphique d'évolution des dépenses
    const evolutionCtx = document.getElementById('depensesEvolutionChart');
    if (evolutionCtx && typeof Chart !== 'undefined') {
        new Chart(evolutionCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct'],
                datasets: [{
                    label: 'Dépenses mensuelles (€)',
                    data: [5200, 6100, 5800, 7200, 6900, 7500, 6800, 6200, 7600, 8547],
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
    
    // Graphique de répartition par catégorie
    const repartitionCtx = document.getElementById('repartitionCategoriesChart');
    if (repartitionCtx && typeof Chart !== 'undefined') {
        new Chart(repartitionCtx, {
            type: 'doughnut',
            data: {
                labels: ['Fournitures', 'Énergie', 'Salaires', 'Services', 'Autres'],
                datasets: [{
                    data: [3419, 2564, 1710, 854, 0],
                    backgroundColor: [
                        'rgb(239, 68, 68)',
                        'rgb(59, 130, 246)',
                        'rgb(34, 197, 94)',
                        'rgb(245, 158, 11)',
                        'rgb(156, 163, 175)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
}

// Exposer les fonctions globalement
window.switchTransactionTab = switchTransactionTab;
window.updateTransactionSummary = updateTransactionSummary;
window.triggerFileUpload = triggerFileUpload;
window.handleFileSelection = handleFileSelection;
window.previewFile = previewFile;
window.removeFile = removeFile;
window.duplicateTransaction = duplicateTransaction;
window.scheduleRecurrence = scheduleRecurrence;
window.deleteTransaction = deleteTransaction;
window.initTransactionModal = initTransactionModal;
window.previewDocument = previewDocument;
window.viewTransactionDetails = viewTransactionDetails;
window.closeTransactionDetailsModal = closeTransactionDetailsModal;

// Fonctions Achats
window.showAchatsTab = showAchatsTab;

/**
 * Gestion Avancée des Catégories - Modal et Fonctions
 */

// Variables globales
let isEditMode = false;
let editingCategoryId = null;

// Base d'icônes par catégorie
const ICON_DATABASE = {
    business: [
        'fas fa-briefcase', 'fas fa-building', 'fas fa-handshake', 'fas fa-users', 
        'fas fa-user-tie', 'fas fa-bullhorn', 'fas fa-chart-line', 'fas fa-chart-bar',
        'fas fa-trophy', 'fas fa-medal', 'fas fa-star', 'fas fa-award'
    ],
    tech: [
        'fas fa-laptop', 'fas fa-desktop', 'fas fa-mobile', 'fas fa-tablet',
        'fas fa-microchip', 'fas fa-cog', 'fas fa-tools', 'fas fa-server',
        'fas fa-database', 'fas fa-code', 'fas fa-wrench', 'fas fa-cpu'
    ],
    logistics: [
        'fas fa-truck', 'fas fa-shipping-fast', 'fas fa-box', 'fas fa-warehouse',
        'fas fa-map-marked-alt', 'fas fa-route', 'fas fa-dolly', 'fas fa-pallet',
        'fas fa-file-invoice', 'fas fa-clipboard-list', 'fas fa-calculator', 'fas fa-barcode'
    ],
    finance: [
        'fas fa-euro-sign', 'fas fa-dollar-sign', 'fas fa-credit-card', 'fas fa-chart-pie',
        'fas fa-piggy-bank', 'fas fa-coins', 'fas fa-money-bill', 'fas fa-hand-holding-usd',
        'fas fa-balance-scale', 'fas fa-receipt', 'fas fa-invoice', 'fas fa-calculator'
    ],
    other: [
        'fas fa-tag', 'fas fa-tags', 'fas fa-bookmark', 'fas fa-flag',
        'fas fa-heart', 'fas fa-star', 'fas fa-gem', 'fas fa-crown',
        'fas fa-lightbulb', 'fas fa-fire', 'fas fa-leaf', 'fas fa-sun'
    ]
};



/**
 * Ouvrir le modal pour éditer une catégorie existante
 */
function editCategory(categoryInput) {
    console.log('✏️ Ouverture modal édition catégorie:', categoryInput);
    
    let categoryData;
    let isEditMode = true;
    
    // Détecter si c'est un ID (nombre) ou un objet complet
    if (typeof categoryInput === 'number' || (typeof categoryInput === 'string' && !isNaN(parseInt(categoryInput)))) {
        // Mode ID : Fetcher les données via API
        const categorieId = parseInt(categoryInput);
        if (isNaN(categorieId) || categorieId <= 0) {
            console.error('❌ ID catégorie invalide:', categoryInput);
            alert('Erreur: ID de catégorie invalide');
            return;
        }
        
        console.log('🔍 Fetch des données pour ID:', categorieId);
        main_loadCategoryData(categorieId).then(() => {
            // Après chargement, déclencher l'ouverture du modal
            openCategoryModal(categorieId);
        }).catch(error => {
            console.error('❌ Erreur lors du fetch:', error);
            alert('Impossible de charger les données de la catégorie');
        });
        
        return; // Sortir pour async
    } else if (typeof categoryInput === 'object' && categoryInput.id) {
        // Mode objet : Remplir directement
        categoryData = categoryInput;
    } else {
        console.error('❌ Argument invalide pour editCategory:', categoryInput);
        alert('Erreur: Données de catégorie invalides');
        return;
    }
    
    // Remplir les champs avec les données existantes (pour mode objet)
    editingCategoryId = categoryData.id;
    
    document.getElementById('categorie-id').value = categoryData.id;
    document.getElementById('categorie-code').value = categoryData.code || '';
    document.getElementById('categorie-nom').value = categoryData.nom || '';
    document.getElementById('categorie-description').value = categoryData.description || '';
    document.getElementById('categorie-icone').value = categoryData.icone || 'fas fa-tag';
    document.getElementById('selected-icon').innerHTML = `<i class="${categoryData.icone || 'fas fa-tag'}"></i>`;
    document.getElementById('categorie-couleur').value = categoryData.couleur || '#3B82F6';
    document.getElementById('couleur-hex').value = categoryData.couleur || '#3B82F6';
    document.getElementById('categorie-ordre').value = categoryData.ordre_affichage || 0;
    document.getElementById('categorie-actif').checked = categoryData.actif !== false;
   
    // Mettre à jour le titre et l'aperçu
    document.getElementById('modal-categorie-title').innerHTML = '<i class="fas fa-edit mr-2"></i>Modifier la Catégorie';
    updateCategoryPreview();
   
    // Afficher le modal
    document.getElementById('categorieModal').classList.remove('hidden');
    document.getElementById('categorieModal').classList.add('flex');
    
    console.log('✅ Modal édition ouvert avec données directes');
}

// ✅ AJOUT : Assurer que main_loadCategoryData et openCategoryModal sont définies (de la réponse précédente)
// Si openCategoryModal n'existe pas, la définir comme fallback
if (typeof openCategoryModal === 'undefined') {
    function openCategoryModal(categorieId = null) {
        const modal = document.getElementById('categorieModal');
        const title = document.getElementById('modal-categorie-title');
        
        if (!modal) {
            console.error('❌ Modal non trouvé');
            return;
        }
        
        if (categorieId) {
            title.innerHTML = '<i class="fas fa-edit mr-2"></i>Modifier la Catégorie';
            // Le fetch est géré dans editCategory maintenant
        } else {
            title.innerHTML = '<i class="fas fa-plus mr-2"></i>Nouvelle Catégorie';
            // Reset form pour création
            document.getElementById('form-categorie').reset();
            document.getElementById('categorie-id').value = '';
            document.getElementById('categorie-ordre').value = '0';
            document.getElementById('categorie-couleur').value = '#3B82F6';
        }
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

// Exposer globalement
window.editCategory = editCategory;



/**
 * Mettre à jour l'aperçu de la catégorie en temps réel
 */
function updateCategoryPreview() {
    const nom = document.getElementById('categorie-nom').value || 'Nom de la catégorie';
    const description = document.getElementById('categorie-description').value || 'Description de la catégorie';
    const icone = document.getElementById('categorie-icone').value || 'fas fa-tag';
    const couleur = document.getElementById('categorie-couleur').value || '#3B82F6';
    const actif = document.getElementById('categorie-actif').checked;
    
    // Mettre à jour l'aperçu
    document.getElementById('preview-nom').textContent = nom;
    document.getElementById('preview-description').textContent = description;
    document.getElementById('preview-icon').innerHTML = `<i class="${icone}"></i>`;
    document.getElementById('preview-icon').style.backgroundColor = couleur;
    document.getElementById('preview-icon').nextElementSibling.nextElementSibling.querySelector('span').textContent = actif ? 'Actif' : 'Inactif';
    document.getElementById('preview-icon').nextElementSibling.nextElementSibling.querySelector('span').className = `px-2 py-1 ${actif ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'} text-xs rounded-full`;
}

/**
 * Ouvrir le sélecteur d'icônes
 */
function openIconSelector() {
    console.log('🎨 Ouverture sélecteur d\'icônes');
    
    // Générer la grille d'icônes
    generateIconGrid('all');
    
    // Afficher le modal
    document.getElementById('iconModal').classList.remove('hidden');
    document.getElementById('iconModal').classList.add('flex');
}

/**
 * Fermer le sélecteur d'icônes
 */
function closeIconModal() {
    document.getElementById('iconModal').classList.add('hidden');
    document.getElementById('iconModal').classList.remove('flex');
}

/**
 * Générer la grille d'icônes avec filtre
 */
function generateIconGrid(filter = 'all') {
    const grid = document.getElementById('icon-grid');
    grid.innerHTML = '';
    
    let icons = [];
    if (filter === 'all') {
        // Toutes les icônes
        Object.values(ICON_DATABASE).forEach(categoryIcons => {
            icons = icons.concat(categoryIcons);
        });
    } else {
        // Icônes de la catégorie sélectionnée
        icons = ICON_DATABASE[filter] || [];
    }
    
    icons.forEach(iconClass => {
        const iconItem = document.createElement('div');
        iconItem.className = 'icon-item';
        iconItem.innerHTML = `<i class="${iconClass}"></i>`;
        iconItem.onclick = () => selectIcon(iconClass);
        
        // Marquer l'icône actuellement sélectionnée
        const currentIcon = document.getElementById('categorie-icone').value;
        if (currentIcon === iconClass) {
            iconItem.classList.add('selected');
        }
        
        grid.appendChild(iconItem);
    });
}

/**
 * Filtrer les icônes par catégorie
 */
function filterIcons(category) {
    // Mettre à jour les boutons de catégorie
    document.querySelectorAll('.icon-category-btn').forEach(btn => {
        btn.className = btn.className.replace('bg-blue-600 text-white', 'bg-gray-200 text-gray-700 hover:bg-gray-300');
    });
    
    event.target.className = event.target.className.replace(/bg-gray-200 text-gray-700 hover:bg-gray-300/, 'bg-blue-600 text-white');
    
    // Générer la grille filtrée
    generateIconGrid(category);
}

/**
 * Sélectionner une icône
 */
function selectIcon(iconClass) {
    console.log('✅ Icône sélectionnée:', iconClass);
    
    // Mettre à jour le champ caché et l'icône sélectionnée
    document.getElementById('categorie-icone').value = iconClass;
    document.getElementById('selected-icon').innerHTML = `<i class="${iconClass}"></i>`;
    
    // Fermer le modal
    closeIconModal();
    
    // Mettre à jour l'aperçu
    updateCategoryPreview();
}

/**
 * Synchroniser les champs couleur (hex et color picker)
 */
function syncColorInputs(changedInput) {
    const colorPicker = document.getElementById('categorie-couleur');
    const hexInput = document.getElementById('couleur-hex');
    
    if (changedInput === 'picker') {
        hexInput.value = colorPicker.value;
    } else if (changedInput === 'hex') {
        const hexValue = hexInput.value;
        if (/^#[0-9A-F]{6}$/i.test(hexValue)) {
            colorPicker.value = hexValue;
        }
    }
    
    updateCategoryPreview();
}

/**
 * Valider le formulaire avant soumission
 */
function validateCategoryForm() {
    const code = document.getElementById('categorie-code').value.trim();
    const nom = document.getElementById('categorie-nom').value.trim();
    
    // Vérifications
    if (!code) {
        alert('Le code est obligatoire');
        return false;
    }
    
    if (code.length > 20) {
        alert('Le code ne peut pas dépasser 20 caractères');
        return false;
    }
    
    if (!nom) {
        alert('Le nom est obligatoire');
        return false;
    }
    
    if (nom.length > 100) {
        alert('Le nom ne peut pas dépasser 100 caractères');
        return false;
    }
    
    // Validation du format de couleur
    const couleur = document.getElementById('couleur-hex').value;
    if (couleur && !/^#[0-9A-F]{6}$/i.test(couleur)) {
        alert('Le format de couleur doit être hexadécimal (ex: #FF0000)');
        return false;
    }
    
    return true;
}

/**
 * Soumettre le formulaire des catégories
 */
async function submitCategoryForm(event) {
    event.preventDefault();
    
    console.log('📤 Soumission formulaire catégorie');
    
    // Validation
    if (!validateCategoryForm()) {
        return;
    }
    
    // Récupérer les données du formulaire
    const formData = new FormData(event.target);
    const categoryData = {
        id: formData.get('id') || null,
        code: formData.get('code').toUpperCase(),
        nom: formData.get('nom'),
        description: formData.get('description') || '',
        icone: formData.get('icone'),
        couleur: formData.get('couleur') || '#3B82F6',
        actif: formData.get('actif') === 'on',
        ordre_affichage: parseInt(formData.get('ordre_affichage')) || 0
    };
    
    console.log('📋 Données catégorie:', categoryData);
    
    try {
        const url = categoryData.id ? 
            'api/categories.php' : 
            'api/categories.php';
        const method = categoryData.id ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(categoryData)
        });
        
        if (!response.ok) {
            throw new Error(`Erreur HTTP ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(
                categoryData.id ? 'Catégorie modifiée avec succès !' : 'Catégorie créée avec succès !',
                'success'
            );
            
            closeCategoryModal();
            
            // Recharger les catégories si nécessaire
            if (typeof loadCategoriesList === 'function') {
                loadCategoriesList();
            }
            
            // Si on est dans l'onglet des achats, recharger aussi cet onglet
            if (typeof loadAchatsCategories === 'function') {
                const activeTab = document.querySelector('.achats-tab.active');
                if (activeTab && activeTab.getAttribute('data-tab') === 'categories') {
                    loadAchatsCategories();
                }
            }
            
        } else {
            throw new Error(result.message || 'Erreur lors de la sauvegarde');
        }
        
    } catch (error) {
        console.error('❌ Erreur sauvegarde catégorie:', error);
        showNotification('Erreur lors de la sauvegarde: ' + error.message, 'error');
    }
}

/**
 * Afficher une notification
 */

// Event listeners pour l'aperçu en temps réel
document.addEventListener('DOMContentLoaded', function() {
    // Écouter les changements sur tous les champs du formulaire
    const form = document.getElementById('form-categorie');
    if (form) {
        // Mettre à jour l'aperçu en temps réel
        form.addEventListener('input', function(e) {
            if (['categorie-nom', 'categorie-description', 'categorie-icone', 'categorie-couleur', 'categorie-actif'].includes(e.target.id)) {
                updateCategoryPreview();
            }
            if (e.target.id === 'couleur-hex' || e.target.id === 'categorie-couleur') {
                syncColorInputs(e.target.id === 'couleur-hex' ? 'hex' : 'picker');
            }
        });
        
        // Le gestionnaire de soumission est déjà configuré plus loin (ligne 8578)
        // Éviter les conflits d'event listeners multiples
    }
    
    // Fermer les modals en cliquant à l'extérieur
    document.getElementById('categorieModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeCategoryModal();
    });
    
    document.getElementById('iconModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeIconModal();
    });
});

// Exposurer les fonctions dans le scope global
window.apiCall = apiCall;
window.openCategoryModal = openCategoryModal;
window.editCategory = editCategory;
window.closeCategoryModal = closeCategoryModal;
window.openIconSelector = openIconSelector;
window.closeIconModal = closeIconModal;
window.filterIcons = filterIcons;
window.selectIcon = selectIcon;
window.syncColorInputs = syncColorInputs;
window.showNotification = showNotification;
window.updateCategoryPreview = updateCategoryPreview;

/**
 * Charger les catégories de dépenses
 */
/**
 * Charger les catégories de dépenses avec données dynamiques
 */
async function loadAchatsCategories() {
    console.log('🏷️ Chargement des catégories de dépenses...');
    
    try {
        const content = document.getElementById('achats-categories-content');
        if (!content) {
            console.error('Conteneur catégories non trouvé');
            return;
        }
        
        // Afficher un spinner de chargement
        content.innerHTML = `
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <span class="ml-3 text-gray-600">Chargement des catégories...</span>
                </div>
            </div>
        `;
        
        // Charger les catégories depuis l'API
        const response = await fetch('api/categories.php?action=list');
        let categories = [];
        
        if (response.ok) {
            try {
                const result = await response.json();
                if (result.success && result.data) {
                    categories = result.data;
                } else {
                    console.warn('API ne retourne pas de données, utilisation des catégories par défaut');
                    categories = getDefaultCategories();
                }
            } catch (jsonError) {
                // Si le JSON échoue, extraire le message d'erreur du HTML
                const errorText = await response.text();
                console.error('Erreur de parsing JSON:', jsonError);
                console.error('Réponse reçue:', errorText);
                
                if (errorText.includes('<br />')) {
                    const match = errorText.match(/<br\s*\/?><b>(.*?)<\/b>/);
                    if (match) {
                        throw new Error(match[1]);
                    } else if (errorText.length < 200) {
                        throw new Error(errorText.replace(/<[^>]*>/g, '').trim());
                    }
                }
                throw new Error('Réponse serveur invalide');
            }
        } else {
            console.warn('Erreur API, utilisation des catégories par défaut');
            categories = getDefaultCategories();
        }
        
        // Générer le HTML des catégories
        const categoriesHTML = generateCategoriesHTML(categories);
        content.innerHTML = categoriesHTML;
        
        console.log('✅ Catégories chargées avec succès:', categories.length);
        
    } catch (error) {
        console.error('❌ Erreur chargement catégories:', error);
        
        // Afficher un message d'erreur avec les catégories par défaut
        const content = document.getElementById('achats-categories-content');
        if (content) {
            const categories = getDefaultCategories();
            const categoriesHTML = generateCategoriesHTML(categories);
            content.innerHTML = categoriesHTML;
            
            // Afficher une notification d'erreur
            showNotification('Impossible de charger les catégories depuis la base de données. Affichage des catégories par défaut.', 'error');
        }
    }
}

/**
 * Catégories par défaut si l'API ne fonctionne pas
 */
function getDefaultCategories() {
    return [
        {
            id: 1,
            code: 'FOURNITURE',
            nom: 'Fournitures',
            description: 'Fournitures de bureau et consommables',
            icone: 'fas fa-pencil-ruler',
            couleur: '#3B82F6',
            actif: true,
            ordre_affichage: 0
        },
        {
            id: 2,
            code: 'ENERGIE',
            nom: 'Énergie',
            description: 'Électricité, gaz, chauffage',
            icone: 'fas fa-bolt',
            couleur: '#F59E0B',
            actif: true,
            ordre_affichage: 1
        },
        {
            id: 3,
            code: 'SALAIRES',
            nom: 'Salaires',
            description: 'Rémunérations et charges sociales',
            icone: 'fas fa-users',
            couleur: '#10B981',
            actif: true,
            ordre_affichage: 2
        },
        {
            id: 4,
            code: 'SERVICES',
            nom: 'Services',
            description: 'Prestations externes et consultants',
            icone: 'fas fa-cogs',
            couleur: '#8B5CF6',
            actif: true,
            ordre_affichage: 3
        },
        {
            id: 5,
            code: 'MAINTENANCE',
            nom: 'Maintenance',
            description: 'Entretien et réparations',
            icone: 'fas fa-tools',
            couleur: '#EF4444',
            actif: true,
            ordre_affichage: 4
        },
        {
            id: 6,
            code: 'MARKETING',
            nom: 'Marketing',
            description: 'Publicité et communication',
            icone: 'fas fa-bullhorn',
            couleur: '#6366F1',
            actif: true,
            ordre_affichage: 5
        }
    ];
}

/**
 * Générer le HTML pour afficher les catégories
 */
function generateCategoriesHTML(categories) {
    const categoryCards = categories.map(cat => `
        <div class="bg-gradient-to-br hover:shadow-lg transition-all duration-300 rounded-lg p-4 border border-gray-200 hover:border-gray-300 cursor-pointer" 
             style="background: linear-gradient(135deg, ${cat.couleur}15, ${cat.couleur}05); border-color: ${cat.couleur}30"
             onclick="editCategory(${JSON.stringify(cat).replace(/"/g, '&quot;')})">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white" style="background-color: ${cat.couleur};">
                        <i class="${cat.icone}"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-800">${cat.nom}</h4>
                        <p class="text-xs text-gray-500">${cat.code}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    ${cat.actif ? 
                        '<span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Actif</span>' : 
                        '<span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">Inactif</span>'
                    }
                </div>
            </div>
            <p class="text-gray-600 text-sm mb-3">${cat.description || 'Aucune description'}</p>
            <div class="flex justify-between items-center text-xs text-gray-500">
                <span>Ordre: ${cat.ordre_affichage}</span>
                <div class="flex items-center space-x-2">
                    <button onclick="event.stopPropagation(); editCategory(${JSON.stringify(cat).replace(/"/g, '&quot;')})" 
                            class="text-blue-600 hover:text-blue-800" title="Modifier">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="event.stopPropagation(); duplicateCategory(${cat.id})" 
                            class="text-green-600 hover:text-green-800" title="Dupliquer">
                        <i class="fas fa-copy"></i>
                    </button>
                    <button onclick="event.stopPropagation(); toggleCategoryStatus(${cat.id})" 
                            class="${cat.actif ? 'text-orange-600 hover:text-orange-800' : 'text-gray-600 hover:text-gray-800'}" 
                            title="${cat.actif ? 'Désactiver' : 'Activer'}">
                        <i class="fas ${cat.actif ? 'fa-pause' : 'fa-play'}"></i>
                    </button>
                </div>
            </div>
        </div>
    `).join('');
    
    return `
        <div class="bg-white rounded-xl shadow-lg p-6">
            <!-- En-tête avec titre et actions -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-xl font-semibold text-gray-800">
                        <i class="fas fa-tags mr-3 text-blue-600"></i>Catégories de Dépenses
                    </h3>
                    <p class="text-gray-600 text-sm mt-1">${categories.length} catégorie${categories.length > 1 ? 's' : ''} configurée${categories.length > 1 ? 's' : ''}</p>
                </div>
                <div class="flex items-center space-x-3">
                    <button onclick="refreshCategories()" class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors" title="Actualiser">
                        <i class="fas fa-sync-alt mr-2"></i>Actualiser
                    </button>
                    <button onclick="openCategoryModal()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i>Nouvelle Catégorie
                    </button>
                </div>
            </div>
            
            <!-- Grille des catégories -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                ${categoryCards}
            </div>
            
            <!-- État vide si aucune catégorie -->
            ${categories.length === 0 ? `
                <div class="text-center py-12">
                    <i class="fas fa-tags text-6xl text-gray-300 mb-4"></i>
                    <h4 class="text-lg font-medium text-gray-600 mb-2">Aucune catégorie trouvée</h4>
                    <p class="text-gray-500 mb-6">Créez votre première catégorie pour organiser vos dépenses</p>
                    <button onclick="openCategoryModal()" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i>Créer une catégorie
                    </button>
                </div>
            ` : ''}
            
            <!-- Statistiques -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-tags text-blue-600 text-2xl mr-3"></i>
                            <div>
                                <p class="text-blue-600 text-sm font-medium">Total Catégories</p>
                                <p class="text-blue-800 text-xl font-bold">${categories.length}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-600 text-2xl mr-3"></i>
                            <div>
                                <p class="text-green-600 text-sm font-medium">Catégories Actives</p>
                                <p class="text-green-800 text-xl font-bold">${categories.filter(c => c.actif).length}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-orange-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-pause-circle text-orange-600 text-2xl mr-3"></i>
                            <div>
                                <p class="text-orange-600 text-sm font-medium">Catégories Inactives</p>
                                <p class="text-orange-800 text-xl font-bold">${categories.filter(c => !c.actif).length}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Actualiser la liste des catégories
 */
function refreshCategories() {
    loadAchatsCategories();
}

/**
 * Dupliquer une catégorie
 */
function duplicateCategory(categoryId) {
    console.log('📋 Duplication catégorie:', categoryId);
    
    // Pour l'instant, affiche un message
    showNotification('Fonction de duplication en développement', 'info');
}

/**
 * Basculer le statut actif/inactif d'une catégorie
 */
async function toggleCategoryStatus(categoryId) {
    console.log('🔄 Changement statut catégorie:', categoryId);
    
    try {
        const response = await fetch(`api/categories.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'toggle_status',
                id: categoryId
            })
        });
        
        if (response.ok) {
            const result = await response.json();
            if (result.success) {
                showNotification('Statut de la catégorie modifié avec succès', 'success');
                refreshCategories();
            } else {
                throw new Error(result.message || 'Erreur lors du changement de statut');
            }
        } else {
            throw new Error('Erreur réseau');
        }
        
    } catch (error) {
        console.error('❌ Erreur changement statut:', error);
        showNotification('Erreur lors du changement de statut: ' + error.message, 'error');
    }
}

/**
 * Charger les rapports d'achats
 */
function loadAchatsRapports() {
    console.log('📊 Chargement des rapports d\'achats...');
    
    try {
        const content = document.getElementById('achats-rapports-content');
        if (!content) {
            console.error('Conteneur rapports non trouvé');
            return;
        }
        
        // Afficher le contenu
        const rapportsHTML = `
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-xl font-semibold mb-6 text-gray-800">
                    <i class="fas fa-chart-bar mr-3 text-green-600"></i>Rapports et Analyses
                </h3>
                
                <!-- Graphiques -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Évolution mensuelle -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-700 mb-4">Évolution Mensuelle</h4>
                        <div class="h-64 bg-white rounded border p-4 flex items-center justify-center">
                            <div class="text-center">
                                <i class="fas fa-chart-line text-4xl text-gray-400 mb-2"></i>
                                <p class="text-gray-500">Graphique d'évolution des dépenses</p>
                                <p class="text-sm text-gray-400 mt-2">Jan - €7,200 | Fév - €8,547 | Mar - €7,890</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Répartition par catégorie -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-700 mb-4">Répartition par Catégorie</h4>
                        <div class="h-64 bg-white rounded border p-4 flex items-center justify-center">
                            <div class="text-center">
                                <i class="fas fa-chart-pie text-4xl text-gray-400 mb-2"></i>
                                <p class="text-gray-500">Répartition des dépenses</p>
                                <div class="text-sm text-gray-400 mt-2 space-y-1">
                                    <div>Salaires: 53%</div>
                                    <div>Fournitures: 15%</div>
                                    <div>Services: 13%</div>
                                    <div>Autres: 19%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Statistiques -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm">Moyenne Mensuelle</p>
                                <p class="text-2xl font-bold">€7,879</p>
                            </div>
                            <i class="fas fa-calculator text-3xl text-blue-200"></i>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-sm">Évolution vs Mois Précédent</p>
                                <p class="text-2xl font-bold">+18.7%</p>
                            </div>
                            <i class="fas fa-arrow-up text-3xl text-green-200"></i>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100 text-sm">Fournisseurs Actifs</p>
                                <p class="text-2xl font-bold">24</p>
                            </div>
                            <i class="fas fa-handshake text-3xl text-purple-200"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Top Fournisseurs -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="font-semibold text-gray-700 mb-4">Top 5 Fournisseurs</h4>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center p-3 bg-white rounded border">
                            <span class="font-medium">Fournitures SARL</span>
                            <span class="text-blue-600 font-semibold">€2,145.80</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-white rounded border">
                            <span class="font-medium">Énergie Plus</span>
                            <span class="text-blue-600 font-semibold">€1,892.30</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-white rounded border">
                            <span class="font-medium">Services Pro</span>
                            <span class="text-blue-600 font-semibold">€1,420.50</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-white rounded border">
                            <span class="font-medium">Maintenance Express</span>
                            <span class="text-blue-600 font-semibold">€834.20</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-white rounded border">
                            <span class="font-medium">Marketing Direct</span>
                            <span class="text-blue-600 font-semibold">€654.50</span>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="mt-6 flex justify-end">
                    <button class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors mr-3">
                        <i class="fas fa-download mr-2"></i>Exporter PDF
                    </button>
                    <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-share mr-2"></i>Partager
                    </button>
                </div>
            </div>
        `;
        
        content.innerHTML = rapportsHTML;
        console.log('✅ Rapports chargés avec succès');
        
    } catch (error) {
        console.error('❌ Erreur chargement rapports:', error);
    }
}
window.updateAchatsDisplay = updateAchatsDisplay;
// window.openPurchaseModal = openPurchaseModal; // DÉPLACÉ - ligne 8204

/**
 * Applique les filtres pour les achats (placeholder)
 */
function applyPurchaseFilters() {
    console.log('🔍 Application des filtres d\'achats...');
    // TODO: Implémenter la logique des filtres d'achats
    showNotification('Filtres d\'achats appliqués (fonctionnalité à implémenter)', 'info');
}

/**
 * Ouvre le modal de gestion des fournisseurs
 */
async function openSupplierModal() {
    try {
        console.log('🏢 Ouverture du modal de gestion des fournisseurs...');
        
        // Charger la liste des fournisseurs
        const response = await apiCall('/tiers.php?type=fournisseur');
        
        let fournisseursHTML = '';
        
        if (response.success && response.data && response.data.length > 0) {
            fournisseursHTML = `
                <div class="space-y-4">
                    ${response.data.map(fournisseur => `
                        <div class="border rounded-lg p-4 bg-white shadow-sm">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h4 class="font-semibold text-gray-800">
                                        ${fournisseur.raison_sociale || 'Fournisseur sans nom'}
                                    </h4>
                                    <p class="text-gray-600 text-sm">Code: ${fournisseur.code || 'N/A'}</p>
                                </div>
                                <div class="flex gap-2">
                                    <button onclick="editSupplier(${fournisseur.id})" class="btn-icon" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteSupplier(${fournisseur.id})" class="btn-icon text-red-600" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        } else {
            fournisseursHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-building text-gray-400 text-4xl mb-4"></i>
                    <p class="text-gray-500">Aucun fournisseur trouvé</p>
                </div>
            `;
        }
        
        // Créer et afficher le modal
        const modalHTML = `
            <div id="supplierModal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="modal-content bg-white rounded-lg max-w-4xl w-full mx-4 max-h-[80vh] overflow-y-auto">
                    <div class="modal-header border-b px-6 py-4 flex justify-between items-center">
                        <h3 class="text-xl font-semibold">
                            <i class="fas fa-building mr-2 text-blue-600"></i>Gestion des Fournisseurs
                        </h3>
                        <button onclick="closeSupplierModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    <div class="modal-body px-6 py-4">
                        <div class="mb-4">
                            <button onclick="addNewSupplier()" class="btn-modern btn-primary-modern">
                                <i class="fas fa-plus mr-2"></i>Nouveau Fournisseur
                            </button>
                        </div>
                        ${fournisseursHTML}
                    </div>
                </div>
            </div>
        `;
        
        // Supprimer l'ancien modal s'il existe
        const oldModal = document.getElementById('supplierModal');
        if (oldModal) {
            oldModal.remove();
        }
        
        // Ajouter le nouveau modal
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        console.log('✅ Modal de gestion des fournisseurs ouvert');
        
    } catch (error) {
        console.error('❌ Erreur lors de l\'ouverture du modal fournisseur:', error);
        showNotification('Erreur lors de l\'ouverture du modal fournisseur', 'error');
    }
}

/**
 * Ferme le modal de gestion des fournisseurs
 */
function closeSupplierModal() {
    const modal = document.getElementById('supplierModal');
    if (modal) {
        modal.remove();
        console.log('✅ Modal de gestion des fournisseurs fermé');
    }
}

/**
 * Ajoute un nouveau fournisseur (placeholder)
 */
function addNewSupplier() {
    alert('Fonctionnalité d\'ajout de fournisseur à implémenter');
    console.log('➕ Ajout d\'un nouveau fournisseur');
}

/**
 * Édite un fournisseur (placeholder)
 */
function editSupplier(id) {
    alert(`Édition du fournisseur #${id} - Fonctionnalité à implémenter`);
    console.log('🔧 Édition du fournisseur:', id);
}

/**
 * Supprime un fournisseur (placeholder)
 */
function deleteSupplier(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce fournisseur ?')) {
        alert(`Suppression du fournisseur #${id} - Fonctionnalité à implémenter`);
        console.log('🗑️ Suppression du fournisseur:', id);
    }
}

window.openSupplierModal = openSupplierModal;
window.applyPurchaseFilters = applyPurchaseFilters;
window.viewPaymentDetails = viewPaymentDetails;
window.markAsPaid = markAsPaid;
window.downloadInvoice = downloadInvoice;
window.viewPurchaseDetails = viewPurchaseDetails;

// === FONCTIONS UTILITAIRES ===
/**
 * Fermer une modal
 */
function closeModal(modalId) {
    try {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.remove();
            console.log('✅ Modal fermée:', modalId);
        }
    } catch (error) {
        console.error('❌ Erreur lors de la fermeture de la modal:', error);
    }
}

/**
 * Prévisualiser un fichier
 */
function previewFile(index) {
    try {
        console.log('👁️ Prévisualisation du fichier:', index);
        showNotification('Fonctionnalité de prévisualisation à implémenter', 'info');
        
        // À implémenter selon vos besoins
        // Ouvrir une modal avec l'aperçu du fichier
        
    } catch (error) {
        console.error('❌ Erreur lors de la prévisualisation:', error);
        showNotification('Erreur lors de la prévisualisation', 'error');
    }
}

// === FONCTIONS DE PAIEMENTS ET FACTURATION ===
/**
 * Afficher les détails d'un paiement
 */
async function viewPaymentDetails(paymentId) {
    try {
        console.log('💳 Affichage des détails du paiement:', paymentId);
        
        // À implémenter selon vos besoins
        const modalHTML = `
            <div id="paymentModal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="modal-content bg-white rounded-lg max-w-2xl w-full mx-4">
                    <div class="p-6">
                        <h2 class="text-xl font-bold mb-4">Détails du Paiement</h2>
                        <p>ID: ${paymentId}</p>
                        <p>Fonctionnalité à implémenter...</p>
                        <div class="flex justify-end gap-2 mt-4">
                            <button onclick="closeModal('paymentModal')" class="px-4 py-2 bg-gray-500 text-white rounded">Fermer</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
    } catch (error) {
        console.error('❌ Erreur:', error);
        showNotification('Erreur lors de l\'affichage des détails', 'error');
    }
}

/**
 * Marquer une facture comme payée
 */
async function markAsPaid(purchaseId) {
    try {
        console.log('✅ Marquage de la facture comme payée:', purchaseId);
        showNotification('Fonctionnalité marquer comme payée à implémenter', 'info');
        
        // À implémenter selon vos besoins
        // Appel API pour mettre à jour le statut de paiement
        
    } catch (error) {
        console.error('❌ Erreur:', error);
        showNotification('Erreur lors de la mise à jour du statut', 'error');
    }
}

/**
 * Télécharger une facture
 */
function downloadInvoice(purchaseId) {
    try {
        console.log('📥 Téléchargement de la facture:', purchaseId);
        showNotification('Fonctionnalité de téléchargement à implémenter', 'info');
        
        // À implémenter selon vos besoins
        // Génération et téléchargement du PDF de facture
        
    } catch (error) {
        console.error('❌ Erreur:', error);
        showNotification('Erreur lors du téléchargement', 'error');
    }
}

/**
 * Afficher les détails d'un achat
 */
async function viewPurchaseDetails(purchaseId) {
    try {
        console.log('📋 Affichage des détails de l\'achat:', purchaseId);
        showNotification('Fonctionnalité d\'affichage des détails à implémenter', 'info');
        
        // À implémenter selon vos besoins
        
    } catch (error) {
        console.error('❌ Erreur:', error);
        showNotification('Erreur lors de l\'affichage des détails', 'error');
    }
}

// === NOUVELLES FONCTIONS MODAL ACHAT ===
/**
 * Ouvre la modal d'ajout d'achat - VERSION UNIFIÉE
 */
function openPurchaseModal() {
    console.log('🛒 Ouverture modal nouvel achat (unifiée)...');
    
    // Afficher la modal
    const modal = document.getElementById('achatModal');
    if (!modal) {
        console.error('❌ Modal avec ID "achatModal" non trouvé');
        return;
    }
    
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    console.log('✅ Modal affichée, réinitialisation du formulaire...');
    
    // Réinitialiser le formulaire
    resetAchatForm();
    
    console.log('📋 Chargement des listes déroulantes...');
    
    // Notification de chargement
    showNotification('Chargement des listes déroulantes...', 'info');
    
    // Charger les listes déroulantes
    loadFournisseursForAchat();
    loadCategoriesForAchat();
    loadComptesForAchat();
    
    // Notification de fin de chargement (après un délai)
    setTimeout(() => {
        showNotification('Modal ouvert - Vérifiez la console pour les détails de chargement', 'success');
    }, 1000);
    
    console.log('✅ Modal affiché avec succès');
    
    // Focus sur le premier champ
    setTimeout(() => {
        const firstInput = document.getElementById('achat-date') || document.getElementById('formNouvelAchat-date');
        if (firstInput) firstInput.focus();
    }, 100);
}

/**
 * Ferme la modal d'ajout d'achat
 */
function closeAchatModal() {
    const modal = document.getElementById('achatModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

/**
 * Réinitialise le formulaire d'achat
 */
function resetAchatForm() {
    console.log('🔄 Réinitialisation du formulaire achat (version unifiée)...');
    
    // Essayer plusieurs IDs de formulaires possibles
    let form = document.getElementById('formNouvelAchat') || 
               document.getElementById('achatForm') ||
               document.querySelector('form[action*="achat"]');
    
    if (!form) {
        console.error('❌ Formulaire d\'achat non trouvé');
        return;
    }
    
    form.reset();
    
    // Définir la date par défaut (aujourd'hui)
    const today = new Date().toISOString().split('T')[0];
    const dateInput = document.getElementById('achat-date') || document.getElementById('formNouvelAchat-date');
    if (dateInput) dateInput.value = today;
    
    // Réinitialiser le statut par défaut
    const statutSelect = document.getElementById('achat-statut') || document.getElementById('formNouvelAchat-statut');
    if (statutSelect) statutSelect.value = 'a_payer';
    
    // Mettre à jour le résumé si la fonction existe
    if (typeof updateAchatSummary === 'function') {
        updateAchatSummary();
    }
    
    console.log('✅ Formulaire réinitialisé avec succès');
}

/**
 * Charge les fournisseurs dans le select de l'achat
 */
async function loadFournisseursForAchat() {
    try {
        console.log('🔄 Chargement des fournisseurs via tiers.php...');
        console.log('🌐 URL complète:', `${window.location.origin}/baba/api/tiers.php?type=fournisseur`);
        
        const response = await fetch(`${window.location.origin}/baba/api/tiers.php?type=fournisseur`);
        
        console.log('📡 Réponse fournisseurs:', response.status, response.statusText);
        console.log('📡 Headers réponse:', [...response.headers.entries()]);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('❌ Erreur HTTP fournisseurs:', errorText);
            
            // Ajouter une option par défaut même en cas d'erreur
            const select = document.getElementById('achat-fournisseur');
            if (select) {
                select.innerHTML = '<option value="">Erreur de chargement - Sélectionner manuellement</option>';
            }
            return;
        }
        
        const data = await response.json();
        console.log('📋 Données fournisseurs reçues:', data);
        
        const select = document.getElementById('achat-fournisseur');
        if (!select) {
            console.error('❌ Select fournisseur non trouvé');
            return;
        }
        
        // Vider le select (garder la première option)
        select.innerHTML = '<option value="">Sélectionner un fournisseur</option>';
        
        if (data.success && data.data && data.data.length > 0) {
            console.log('✅ Ajout des fournisseurs:', data.data.length);
            console.log('🔍 Debug - Structure du premier fournisseur:', data.data[0]);
            console.log('🔍 Debug - Toutes les propriétés:', Object.keys(data.data[0] || {}));
            
            data.data.forEach((fournisseur, index) => {
                console.log(`🔍 Debug fournisseur ${index + 1}:`, fournisseur);
                
                const option = document.createElement('option');
                option.value = fournisseur.id || fournisseur.ID || fournisseur.tiers_id;
                
                // Essayer différentes propriétés possibles pour le nom
                const nomFournisseur = fournisseur.raison_sociale || 
                                     fournisseur.nom || 
                                     fournisseur.raison || 
                                     fournisseur.nom_raison_sociale ||
                                     fournisseur.display_name ||
                                     `${fournisseur.nom || ''} ${fournisseur.prenom || ''}`.trim() ||
                                     'Fournisseur sans nom';
                                     
                option.textContent = nomFournisseur;
                select.appendChild(option);
                
                console.log(`✅ Fournisseur ${index + 1} ajouté:`, nomFournisseur);
            });
            console.log('✅ Fournisseurs chargés avec succès');
        } else {
            console.warn('⚠️ Aucune donnée de fournisseur reçue, ajout option par défaut');
            const option = document.createElement('option');
            option.value = '1';
            option.textContent = 'Fournisseur par défaut';
            select.appendChild(option);
        }
    } catch (error) {
        console.error('❌ Erreur lors du chargement des fournisseurs:', error);
        
        // En cas d'erreur, ajouter des options de fallback
        const select = document.getElementById('achat-fournisseur');
        if (select) {
            select.innerHTML = '<option value="">Erreur de connexion - Sélectionner manuellement</option>';
            const fallbackOptions = [
                { value: '1', text: 'Fournisseur par défaut 1' },
                { value: '2', text: 'Fournisseur par défaut 2' },
                { value: '3', text: 'Fournisseur par défaut 3' }
            ];
            fallbackOptions.forEach(fournisseur => {
                const option = document.createElement('option');
                option.value = fournisseur.value;
                option.textContent = fournisseur.text;
                select.appendChild(option);
            });
        }
    }
}

/**
 * Charge les catégories dans le select de l'achat
 */
async function loadCategoriesForAchat() {
    try {
        console.log('🔄 Chargement des catégories via categories.php...');
        console.log('🌐 URL complète:', `${window.location.origin}/baba/api/categories.php?action=list`);
        
        const response = await fetch(`${window.location.origin}/baba/api/categories.php?action=list`);
        
        console.log('📡 Réponse catégories:', response.status, response.statusText);
        console.log('📡 Headers réponse:', [...response.headers.entries()]);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('❌ Erreur HTTP catégories:', errorText);
            
            // Ajouter une option par défaut même en cas d'erreur
            const select = document.getElementById('achat-categorie-pieces');
            if (select) {
                select.innerHTML = '<option value="">Erreur de chargement - Sélectionner manuellement</option>';
            }
            return;
        }
        
        const data = await response.json();
        console.log('📋 Données catégories reçues:', data);
        
        const select = document.getElementById('achat-categorie-pieces');
        if (!select) {
            console.error('❌ Select catégorie non trouvé');
            return;
        }
        
        // Vider le select (garder la première option)
        select.innerHTML = '<option value="">Sélectionner une catégorie</option>';
        
        if (data.success && data.data && data.data.length > 0) {
            console.log('✅ Ajout des catégories:', data.data.length);
            data.data.forEach(categorie => {
                const option = document.createElement('option');
                option.value = categorie.id;
                option.textContent = categorie.nom;
                select.appendChild(option);
            });
            console.log('✅ Catégories chargées avec succès');
        } else {
            console.warn('⚠️ Aucune donnée de catégorie reçue, ajout options par défaut');
            const fallbackCategories = [
                { value: '1', text: 'Fournitures de bureau' },
                { value: '2', text: 'Matériel informatique' },
                { value: '3', text: 'Services et maintenance' },
                { value: '4', text: 'Équipements' },
                { value: '5', text: 'Consommables' }
            ];
            fallbackCategories.forEach(categorie => {
                const option = document.createElement('option');
                option.value = categorie.value;
                option.textContent = categorie.text;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('❌ Erreur lors du chargement des catégories:', error);
        
        // En cas d'erreur, ajouter des options de fallback
        const select = document.getElementById('achat-categorie-pieces');
        if (select) {
            select.innerHTML = '<option value="">Erreur de connexion - Sélectionner manuellement</option>';
            const fallbackCategories = [
                { value: '1', text: 'Fournitures de bureau' },
                { value: '2', text: 'Matériel informatique' },
                { value: '3', text: 'Services et maintenance' },
                { value: '4', text: 'Équipements' },
                { value: '5', text: 'Consommables' }
            ];
            fallbackCategories.forEach(categorie => {
                const option = document.createElement('option');
                option.value = categorie.value;
                option.textContent = categorie.text;
                select.appendChild(option);
            });
        }
    }
}

/**
 * Charge les comptes dans le select de l'achat
 */
async function loadComptesForAchat() {
    try {
        const response = await fetch('api/comptes.php');
        const data = await response.json();
        
        const select = document.getElementById('achat-compte');
        if (!select) return;
        
        // Vider le select (garder la première option)
        select.innerHTML = '<option value="">Sélectionner un compte</option>';
        
        if (data.success && data.data) {
            data.data.forEach(compte => {
                if (compte.is_active) {
                    const option = document.createElement('option');
                    option.value = compte.id;
                    option.textContent = `${compte.name} (${compte.type === 'banque' ? 'Banque' : 'Caisse'})`;
                    select.appendChild(option);
                }
            });
        }
    } catch (error) {
        console.error('Erreur lors du chargement des comptes:', error);
    }
}

/**
 * Met à jour le résumé de l'achat en temps réel
 */
function updateAchatSummary() {
    // Montant
    const montantInput = document.getElementById('achat-montant');
    const montant = montantInput ? parseFloat(montantInput.value) || 0 : 0;
    updateSummaryField('achat-summary-montant', `${montant.toFixed(2)} €`);
    
    // Fournisseur
    const fournisseurSelect = document.getElementById('achat-fournisseur');
    const fournisseurText = fournisseurSelect ? 
        (fournisseurSelect.selectedIndex > 0 ? fournisseurSelect.options[fournisseurSelect.selectedIndex].text : '-') : '-';
    updateSummaryField('achat-summary-fournisseur', fournisseurText);
    
    // Mode de paiement
    const modeSelect = document.getElementById('achat-mode-paiement');
    const modeText = modeSelect ? 
        (modeSelect.selectedIndex > 0 ? modeSelect.options[modeSelect.selectedIndex].text.replace(/[💵📝🏦💳🔄🇪🇺📋📄💎⚙️]/g, '').trim() : '-') : '-';
    updateSummaryField('achat-summary-mode', modeText);
    
    // Date de paiement
    const datePaiementInput = document.getElementById('achat-date-paiement');
    const datePaiement = datePaiementInput ? datePaiementInput.value : '';
    updateSummaryField('achat-summary-date-paiement', datePaiement || '-');
    
    // Statut
    const statutSelect = document.getElementById('achat-statut');
    const statutText = statutSelect ? 
        (statutSelect.selectedIndex > 0 ? statutSelect.options[statutSelect.selectedIndex].text : '⏳ À payer') : '⏳ À payer';
    updateSummaryField('achat-summary-statut', statutText);
    
    // Catégorie
    const categorieSelect = document.getElementById('achat-categorie-pieces');
    const categorieText = categorieSelect ? 
        (categorieSelect.selectedIndex > 0 ? categorieSelect.options[categorieSelect.selectedIndex].text : '-') : '-';
    updateSummaryField('achat-summary-categorie', categorieText);
}

/**
 * Met à jour un champ de résumé
 */
function updateSummaryField(fieldId, value) {
    const field = document.getElementById(fieldId);
    if (field) field.textContent = value;
}

/**
 * Gère la sélection de fichier pour la facture
 */
function handleAchatFileSelection() {
    const fileInput = document.getElementById('achat-fichier');
    if (fileInput && fileInput.files.length > 0) {
        const file = fileInput.files[0];
        
        // Vérifier la taille (max 10MB)
        if (file.size > 10 * 1024 * 1024) {
            showNotification('Le fichier est trop volumineux (max 10MB)', 'error');
            fileInput.value = '';
            return;
        }
        
        // Vérifier le type
        const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (!allowedTypes.includes(file.type)) {
            showNotification('Type de fichier non supporté', 'error');
            fileInput.value = '';
            return;
        }
        
        showNotification(`Fichier sélectionné: ${file.name}`, 'success');
    }
}

/**
 * Sauvegarde un achat
 * @param {boolean} continueAfter - Si true, continue avec un nouveau formulaire
 */
async function saveAchat(continueAfter = false) {
    console.log('💾 Sauvegarde de l\'achat...', { continueAfter });
    
    // Valider le formulaire
    if (!validateAchatForm()) {
        return;
    }
    
    const formData = getAchatFormData();
    console.log('📋 Données de l\'achat:', formData);
    
    try {
        const response = await fetch('api/pieces_tresorerie.php?path=create_achat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Achat enregistré avec succès!', 'success');
            
            // Télécharger le fichier s'il y en a un
            const fileInput = document.getElementById('achat-fichier');
            if (fileInput && fileInput.files.length > 0) {
                await uploadAchatDocument(result.data.CleDocument, fileInput.files[0]);
            }
            
            if (continueAfter) {
                // Réinitialiser pour un nouvel achat
                resetAchatForm();
            } else {
                // Fermer la modal
                closeAchatModal();
            }
            
            // Rafraîchir les données des achats
            updateAchatsDisplay();
            
        } else {
            console.error('Erreur lors de la sauvegarde:', result.message);
            showNotification(`Erreur: ${result.message}`, 'error');
        }
        
    } catch (error) {
        console.error('Erreur lors de la sauvegarde de l\'achat:', error);
        showNotification('Erreur lors de la sauvegarde de l\'achat', 'error');
    }
}

/**
 * Valide le formulaire d'achat
 */
function validateAchatForm() {
    const requiredFields = [
        { id: 'achat-date', name: 'Date' },
        { id: 'achat-fournisseur', name: 'Fournisseur' },
        { id: 'achat-description', name: 'Description' },
        { id: 'achat-montant', name: 'Montant' },
        { id: 'achat-compte', name: 'Compte' }
    ];
    
    for (const field of requiredFields) {
        const element = document.getElementById(field.id);
        if (!element || !element.value.trim()) {
            showNotification(`Le champ "${field.name}" est requis`, 'error');
            if (element) element.focus();
            return false;
        }
    }
    
    // Validation du montant
    const montant = parseFloat(document.getElementById('achat-montant').value);
    if (isNaN(montant) || montant <= 0) {
        showNotification('Le montant doit être supérieur à 0', 'error');
        document.getElementById('achat-montant').focus();
        return false;
    }
    
    return true;
}

/**
 * Récupère les données du formulaire d'achat
 */
function getAchatFormData() {
    return {
        // Mappage vers la structure pieces_tresorerie
        CleTiers: document.getElementById('achat-fournisseur').value || null,
        Date: document.getElementById('achat-date').value,
        Date2: document.getElementById('achat-date-paiement').value || null, // Date d'échéance
        Label: document.getElementById('achat-description').value.trim(),
        Note: document.getElementById('achat-note-interne').value.trim() || null,
        MontantTTC: parseFloat(document.getElementById('achat-montant').value),
        Reference: document.getElementById('achat-reference').value.trim() || null,
        Payement: document.getElementById('achat-mode-paiement').value || null,
        CleCompte: document.getElementById('achat-compte').value || null,
        CleMode: document.getElementById('achat-mode-paiement').value || null,
        CleDevise: 'XAF',
        TauxChange: 1.0,
        CleTypeDocument: 'facture_achat',
        CleEtatDocument: 'brouillon',
        DateEtat: new Date().toISOString().split('T')[0],
        bModeTTC: true,
        // Calculs automatiques pour les champs optionnels
        MontantHT: null, // Sera calculé automatiquement par l'API
        TotalTVA: null,  // Sera calculé automatiquement par l'API
        RemisePourcent: 0,
        Remise: 0,
        Timbre: 0,
        Marge: null,
        Cloture: false,
        RefAssocie: null,
        CleUser: null,
        CleCommercial: null,
        BaseRistourne: null
    };
}

/**
 * Upload un document pour un achat
 */
async function uploadAchatDocument(transactionId, file) {
    try {
        const formData = new FormData();
        formData.append('transaction_id', transactionId);
        formData.append('document', file);
        
        const response = await fetch('api/upload_document.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (!result.success) {
            console.error('Erreur lors de l\'upload:', result.message);
            showNotification('Achat enregistré mais erreur lors de l\'upload de la facture', 'warning');
        }
        
    } catch (error) {
        console.error('Erreur lors de l\'upload de la facture:', error);
        showNotification('Achat enregistré mais erreur lors de l\'upload de la facture', 'warning');
    }
}

// ========== MODAL NOUVEL ACHAT AVEC ONGLETS ==========

// Variables globales pour le modal
let currentAchatData = {};

// Navigation entre onglets
function switchAchatTab(tabName) {
    console.log('🔄 Changement d\'onglet vers:', tabName);
    
    // Masquer tous les onglets du modal achat uniquement
    document.querySelectorAll('#achatModal .tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Désactiver tous les boutons du modal achat uniquement
    document.querySelectorAll('#achatModal .tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Activer l'onglet sélectionné
    const targetTab = document.getElementById(`tab-${tabName}`);
    if (targetTab) {
        targetTab.classList.add('active');
        console.log('✅ Onglet activé:', targetTab.id);
        
        // Mettre à jour le bouton correspondant
        const targetBtn = document.querySelector(`#achatModal .tab-btn[onclick*="${tabName}"]`);
        if (targetBtn) {
            targetBtn.classList.add('active');
            console.log('✅ Bouton activé:', targetBtn.textContent.trim());
        }
        
        // Mettre à jour le récapitulatif si nécessaire
        if (tabName === 'main-info' || tabName === 'financial') {
            setTimeout(() => updateAchatRecap(), 100);
        }
    } else {
        console.error('❌ Onglet non trouvé:', `tab-${tabName}`);
        // Lister tous les onglets disponibles pour debug
        const allTabs = document.querySelectorAll('#achatModal .tab-content');
        console.log('Onglets disponibles:', Array.from(allTabs).map(t => t.id));
    }
}

// Calculs financiers automatiques
function calculateFinancials() {
    const montantHT = parseFloat(document.getElementById('achat-montant-ht')?.value) || 0;
    const tauxTVA = parseFloat(document.getElementById('achat-taux-tva')?.value) || 0;
    const remise = parseFloat(document.getElementById('achat-remise')?.value) || 0;
    const timbre = parseFloat(document.getElementById('achat-timbre')?.value) || 0;
    
    // Calculs
    const totalTVA = montantHT * (tauxTVA / 100);
    const montantTTC = (montantHT + totalTVA - remise + timbre);
    
    // Affichage
    const tvaElement = document.getElementById('achat-tva-calcule');
    const ttcElement = document.getElementById('achat-ttc-calcule');
    
    if (tvaElement) tvaElement.textContent = formatEuro(totalTVA);
    if (ttcElement) ttcElement.textContent = formatEuro(montantTTC);
}

// Mise à jour du statut de paiement
function updatePaymentStatus() {
    const statut = document.getElementById('achat-statut')?.value;
    const paymentRow = document.getElementById('payment-date-row');
    
    if (paymentRow) {
        if (statut === 'paye') {
            paymentRow.style.display = 'flex';
        } else {
            paymentRow.style.display = 'none';
        }
    }
}

// Collecte des données du formulaire
function collectAchatData() {
    return {
        // Informations principales
        CleTypeDocument: 'facture_achat',
        CleTiers: document.getElementById('achat-fournisseur')?.value,
        Label: document.getElementById('achat-description')?.value,
        Date: document.getElementById('achat-date-facture')?.value,
        DateEcheance: document.getElementById('achat-date-echeance')?.value,
        
        // Détails financiers
        MontantHT: parseFloat(document.getElementById('achat-montant-ht')?.value) || 0,
        TauxTVA: parseFloat(document.getElementById('achat-taux-tva')?.value) || 0,
        TotalTVA: parseFloat(document.getElementById('achat-tva-calcule')?.textContent?.replace(/[^\d.-]/g, '')) || 0,
        MontantTTC: parseFloat(document.getElementById('achat-ttc-calcule')?.textContent?.replace(/[^\d.-]/g, '')) || 0,
        Remise: parseFloat(document.getElementById('achat-remise')?.value) || 0,
        Timbre: parseFloat(document.getElementById('achat-timbre')?.value) || 0,
        
        // Paiement
        Payement: document.getElementById('achat-mode-paiement')?.value,
        CleCompte: document.getElementById('achat-compte')?.value,
        CleEtatDocument: document.getElementById('achat-statut')?.value,
        DatePaiement: document.getElementById('achat-date-paiement')?.value,
        
        // Documents et notes
        Note: document.getElementById('achat-notes-internes')?.value,
        MotsCles: document.getElementById('achat-mots-cles')?.value,
        documents: uploadedFiles
    };
}

// Validation du formulaire
function validateAchatForm(data) {
    if (!data.CleTiers) {
        alert('Veuillez sélectionner un fournisseur');
        return false;
    }
    
    if (!data.Label) {
        alert('Veuillez saisir une description');
        return false;
    }
    
    if (!data.Date) {
        alert('Veuillez sélectionner une date de facture');
        return false;
    }
    
    if (data.MontantHT <= 0) {
        alert('Le montant HT doit être supérieur à 0');
        return false;
    }
    
    if (!data.CleCompte) {
        alert('Veuillez sélectionner un compte');
        return false;
    }
    
    return true;
}

// Sauvegarde de l'achat
async function saveAchat(event) {
    if (event) event.preventDefault();
    
    try {
        // Collecte des données
        const formData = collectAchatData();
        
        // Validation
        if (!validateAchatForm(formData)) {
            return;
        }
        
        // Envoi à l'API
        const response = await fetch(`${API_BASE}/pieces_tresorerie.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'create_achat',
                ...formData
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Achat enregistré avec succès!');
            closeAchatModal();
            // Recharger la page pour afficher le nouvel achat
            location.reload();
        } else {
            throw new Error(result.message || 'Erreur lors de l\'enregistrement');
        }
        
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de l\'enregistrement: ' + error.message);
    }
}

// Mise à jour du récapitulatif
function updateAchatRecap() {
    // Récupérer les valeurs des champs
    const fournisseur = document.getElementById('achat-fournisseur');
    const date = document.getElementById('achat-date-facture');
    const montantHT = document.getElementById('achat-montant-ht');
    
    // Mettre à jour les récapitulatifs selon l'onglet actif
    if (fournisseur && fournisseur.value) {
        const recapFournisseur = document.getElementById('recap-fournisseur');
        if (recapFournisseur) {
            recapFournisseur.textContent = 'Fournisseur: ' + fournisseur.options[fournisseur.selectedIndex].text;
        }
    }
    
    if (date && date.value) {
        const recapDate = document.getElementById('recap-date');
        if (recapDate) {
            recapDate.textContent = 'Date: ' + formatDate(date.value);
        }
    }
    
    if (montantHT && montantHT.value) {
        const recapHT = document.getElementById('recap-ht');
        if (recapHT) {
            recapHT.textContent = 'HT: ' + formatEuro(parseFloat(montantHT.value));
        }
    }
}

// Fonction utilitaire pour formater la taille des fichiers
function formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Gestion de l'upload de fichiers
function handleDocumentUpload(files) {
    const uploadedFilesDiv = document.getElementById('uploaded-files');
    
    if (!uploadedFilesDiv) return;
    
    Array.from(files).forEach(file => {
        uploadedFiles.push(file);
        
        // Affichage du fichier avec les nouveaux styles
        const fileDiv = document.createElement('div');
        fileDiv.className = 'uploaded-file';
        
        // Déterminer l'icône en fonction du type de fichier
        let fileIcon = 'fas fa-file';
        if (file.type.startsWith('image/')) {
            fileIcon = 'fas fa-file-image';
        } else if (file.type === 'application/pdf') {
            fileIcon = 'fas fa-file-pdf';
        } else if (file.type.includes('word') || file.name.endsWith('.doc') || file.name.endsWith('.docx')) {
            fileIcon = 'fas fa-file-word';
        } else if (file.type.includes('excel') || file.name.endsWith('.xls') || file.name.endsWith('.xlsx')) {
            fileIcon = 'fas fa-file-excel';
        }
        
        const fileSize = formatFileSize(file.size);
        
        fileDiv.innerHTML = `
            <div class="uploaded-file-info">
                <i class="${fileIcon}"></i>
                <div>
                    <div class="uploaded-file-name">${file.name}</div>
                    <div class="uploaded-file-size">${fileSize}</div>
                </div>
            </div>
            <button type="button" class="remove-file-btn" onclick="removeUploadedFile('${file.name}')">
                <i class="fas fa-times"></i> Supprimer
            </button>
        `;
        
        uploadedFilesDiv.appendChild(fileDiv);
    });
}

// Suppression d'un fichier uploadé avec animation
function removeUploadedFile(fileName) {
    // Trouver et animer la suppression
    const fileElements = document.querySelectorAll('.uploaded-file');
    fileElements.forEach(element => {
        if (element.textContent.includes(fileName)) {
            element.style.animation = 'slideOutRight 0.3s ease forwards';
            setTimeout(() => {
                uploadedFiles = uploadedFiles.filter(file => file.name !== fileName);
                updateUploadedFilesDisplay();
            }, 300);
            return;
        }
    });
}

// Fonction pour mettre à jour l'affichage des fichiers uploadés
function updateUploadedFilesDisplay() {
    const uploadedFilesDiv = document.getElementById('uploaded-files');
    if (uploadedFilesDiv) {
        uploadedFilesDiv.innerHTML = '';
        uploadedFiles.forEach(file => {
            const fileDiv = document.createElement('div');
            fileDiv.className = 'uploaded-file';
            fileDiv.innerHTML = `
                <span>${file.name}</span>
                <button type="button" onclick="removeUploadedFile('${file.name}')">
                    <i class="fas fa-times"></i>
                </button>
            `;
            uploadedFilesDiv.appendChild(fileDiv);
        });
    }
}

// Fonctions utilitaires
function formatEuro(amount) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    }).format(amount);
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('fr-FR');
}

// Fonctions d'ouverture/fermeture du modal
function openAchatModal() {
    console.log('📋 openAchatModal() redirigé vers openPurchaseModal()');
    openPurchaseModal();
}

function closeAchatModal() {
    console.log('🔒 Fermeture modal achat...');
    const modal = document.getElementById('achatModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        console.log('✅ Modal fermée');
    }

}

// SUPPRIMÉ - Doublon de resetAchatForm()
// La version unifiée est définie plus haut

// Chargement des listes déroulantes
async function loadAchatDropdowns() {
    try {
        // Charger les fournisseurs
        const tiersResponse = await fetch(`${API_BASE}/tiers.php`);
        const tiersData = await tiersResponse.json();
        
        if (tiersData.success) {
            const select = document.getElementById('achat-fournisseur');
            if (select) {
                select.innerHTML = '<option value="">Sélectionner un fournisseur</option>';
                
                tiersData.data.forEach(tier => {
                    const option = document.createElement('option');
                    option.value = tier.id;
                    option.textContent = tier.libelle;
                    select.appendChild(option);
                });
            }
        }
        
        // Charger les comptes
        const comptesResponse = await fetch(`${API_BASE}/comptes.php`);
        const comptesData = await comptesResponse.json();
        
        if (comptesData.success) {
            const select = document.getElementById('achat-compte');
            if (select) {
                select.innerHTML = '<option value="">Sélectionner un compte</option>';
                
                comptesData.data.forEach(compte => {
                    const option = document.createElement('option');
                    option.value = compte.id;
                    option.textContent = compte.libelle;
                    select.appendChild(option);
                });
            }
        }
        
    } catch (error) {
        console.error('Erreur lors du chargement des listes:', error);
    }
}

// Fonctions d'action supplémentaires
function duplicateCurrentAchat() {
    // Récupérer les données du formulaire actuel
    const formData = new FormData(document.getElementById('formNouvelAchat'));
    const achatData = {};
    
    // Extraire les valeurs des champs principaux
    for (let [key, value] of formData.entries()) {
        if (value) achatData[key] = value;
    }
    
    // Ajouter "_copie" au nom pour indiquer la duplication
    if (achatData['achat-description']) {
        achatData['achat-description'] += ' (Copie)';
    }
    
    // Afficher un message de confirmation avec les données à dupliquer
    let confirmation = 'Dupliquer cet achat ?\n\n';
    confirmation += 'Fournisseur: ' + (achatData['achat-fournisseur'] || 'Non sélectionné') + '\n';
    confirmation += 'Description: ' + (achatData['achat-description'] || 'Non renseignée') + '\n';
    confirmation += 'Montant HT: ' + (achatData['achat-montant-ht'] || '0') + ' €\n';
    
    if (confirm(confirmation)) {
        showNotification('Données préparées pour duplication', 'success');
        
        // Pré-remplir le formulaire avec les données dupliquées après fermeture et réouverture
        setTimeout(() => {
            // Reset puis pré-remplir
            resetAchatForm();
            prefillAchatForm(achatData);
        }, 500);
    }
}

function exportAchatData() {
    // Récupérer les données du formulaire
    const formData = new FormData(document.getElementById('formNouvelAchat'));
    const achatData = {};
    
    for (let [key, value] of formData.entries()) {
        if (value) achatData[key] = value;
    }
    
    // Créer un objet de données complet
    const exportData = {
        type: 'Nouvel Achat',
        timestamp: new Date().toLocaleString('fr-FR'),
        data: {
            fournisseur: achatData['achat-fournisseur'] || '',
            description: achatData['achat-description'] || '',
            dateFacture: achatData['achat-date-facture'] || '',
            dateEcheance: achatData['achat-date-echeance'] || '',
            montantHT: achatData['achat-montant-ht'] || '0',
            tauxTVA: achatData['achat-taux-tva'] || '20',
            remise: achatData['achat-remise'] || '0',
            timbre: achatData['achat-timbre'] || '0',
            modePaiement: achatData['achat-mode-paiement'] || '',
            compte: achatData['achat-compte'] || '',
            statut: achatData['achat-statut'] || '',
            notesInternes: achatData['achat-notes-internes'] || '',
            motsCles: achatData['achat-mots-cles'] || ''
        }
    };
    
    // Créer et télécharger le fichier JSON
    const dataStr = JSON.stringify(exportData, null, 2);
    const dataBlob = new Blob([dataStr], { type: 'application/json' });
    const url = URL.createObjectURL(dataBlob);
    
    const link = document.createElement('a');
    link.href = url;
    link.download = `achat_${new Date().toISOString().split('T')[0]}.json`;
    link.click();
    
    URL.revokeObjectURL(url);
    showNotification('Données exportées avec succès', 'success');
}

// Fonction utilitaire pour pré-remplir le formulaire
function prefillAchatForm(data) {
    Object.keys(data).forEach(key => {
        const element = document.getElementById(key);
        if (element) {
            element.value = data[key];
        }
    });
    
    // Recalculer les montants financiers si nécessaire
    if (data['achat-montant-ht']) {
        setTimeout(() => calculateFinancials(), 100);
    }
}

// Fonction pour charger les fournisseurs depuis la base de données
async function loadFournisseurs() {
    console.log('🏪 Chargement des fournisseurs depuis la base...');
    
    const selectElement = document.getElementById('achat-fournisseur');
    if (!selectElement) return;
    
    try {
        // Appel API pour récupérer les fournisseurs
        const response = await fetch(`${API_BASE}/tiers.php?type=fournisseur`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            console.log('✅ Fournisseurs chargés:', data);
            
            // Vider le select existant (garder la première option)
            selectElement.innerHTML = '<option value="">Sélectionner un fournisseur</option>';
            
            // Ajouter chaque fournisseur
            if (data.success && data.data) {
                data.data.forEach(fournisseur => {
                    const option = document.createElement('option');
                    option.value = fournisseur.id;
                    option.textContent = `${fournisseur.nom || fournisseur.raison_sociale} (${fournisseur.categorie || 'Fournisseur'})`;
                    selectElement.appendChild(option);
                });
            }
            
            console.log(`✅ ${selectElement.options.length - 1} fournisseurs chargés`);
        } else {
            console.error('❌ Erreur lors du chargement des fournisseurs');
            // Fallback avec quelques fournisseurs par défaut
            const fallbackFournisseurs = [
                { id: 'F001', nom: 'Fournisseur Principal' },
                { id: 'F002', nom: 'Fournisseur Secondaire' },
                { id: 'F003', nom: 'Prestataire Services' }
            ];
            
            fallbackFournisseurs.forEach(fournisseur => {
                const option = document.createElement('option');
                option.value = fournisseur.id;
                option.textContent = fournisseur.nom;
                selectElement.appendChild(option);
            });
        }
    } catch (error) {
        console.error('❌ Erreur réseau lors du chargement des fournisseurs:', error);
        // Fallback local
        const localFournisseurs = [
            { id: 'F001', nom: 'Fournisseur Principal (Fallback)' },
            { id: 'F002', nom: 'Fournisseur Secondaire (Fallback)' }
        ];
        
        localFournisseurs.forEach(fournisseur => {
            const option = document.createElement('option');
            option.value = fournisseur.id;
            option.textContent = fournisseur.nom;
            selectElement.appendChild(option);
        });
    }
}

// Fonction pour charger les catégories depuis la base de données
async function loadCategories() {
    console.log('📦 Chargement des catégories depuis la base...');
    
    const selectElement = document.getElementById('achat-categorie-pieces');
    if (!selectElement) {
        console.error('❌ Select catégories non trouvé');
        return;
    }
    
    try {
        // API_BASE est déjà définie globalement
        const response = await fetch(`${API_BASE}/categories.php`);
        
        if (!response.ok) {
            // Essayer de parser la réponse en JSON, sinon extraire le message d'erreur du HTML
            let errorMessage = `Erreur HTTP ${response.status}`;
            try {
                const errorData = await response.json();
                errorMessage = errorData.message || errorData.error || errorMessage;
            } catch (jsonError) {
                // Si le JSON échoue, extraire le message d'erreur du HTML
                const errorText = await response.text();
                if (errorText.includes('<br />')) {
                    const match = errorText.match(/<br\s*\/?><b>(.*?)<\/b>/);
                    if (match) {
                        errorMessage = match[1];
                    } else if (errorText.length < 200) {
                        errorMessage = errorText.replace(/<[^>]*>/g, '').trim();
                    }
                }
            }
            throw new Error(errorMessage);
        }
        
        const data = await response.json();
        console.log('📊 Catégories reçues:', data);
        
        if (data.success && Array.isArray(data.data)) {
            // Vider la liste actuelle sauf l'option par défaut
            selectElement.innerHTML = '<option value="">Sélectionner une catégorie</option>';
            
            // Ajouter les catégories de la base
            data.data.forEach(categorie => {
                const option = document.createElement('option');
                option.value = categorie.code;
                option.textContent = categorie.nom;
                option.title = categorie.description || '';
                selectElement.appendChild(option);
            });
            
            console.log(`✅ ${data.data.length} catégories chargées`);
        } else {
            throw new Error('Format de données invalide');
        }
        
    } catch (error) {
        console.error('❌ Erreur réseau lors du chargement des catégories:', error);
        
        // Fallback: catégories par défaut
        selectElement.innerHTML = `
            <option value="">Sélectionner une catégorie</option>
            <option value="FOURNITURE">📦 Fournitures</option>
            <option value="EQUIPEMENT">🖥️ Équipement</option>
            <option value="MAINTENANCE">🔧 Maintenance</option>
            <option value="SERVICES">💼 Services</option>
            <option value="TRAVAUX">🏗️ Travaux</option>
            <option value="CONSOMMABLE">🛒 Consommables</option>
            <option value="AUTRES">📋 Autres</option>
        `;
        console.log('⚠️ Utilisation catégories par défaut');
    }
}

// Fonction pour calculer HT à partir du TTC
function calculateFinancialsFromTTC() {
    const montantTTC = parseFloat(document.getElementById('achat-montant-ttc')?.value || 0);
    const tauxTVA = parseFloat(document.getElementById('achat-taux-tva')?.value || 20);
    
    if (montantTTC > 0) {
        const montantHT = montantTTC / (1 + (tauxTVA / 100));
        const montantTVA = montantTTC - montantHT;
        
        // Mettre à jour les champs de l'onglet financier
        document.getElementById('achat-montant-ht').value = montantHT.toFixed(2);
        document.getElementById('achat-montant-ttc-display').value = montantTTC.toFixed(2);
        
        console.log(`💰 Calcul TTC→HT: ${montantTTC.toFixed(2)}€ → ${montantHT.toFixed(2)}€ HT + ${montantTVA.toFixed(2)}€ TVA (${tauxTVA}%)`);
        
        // Mettre à jour le récapitulatif
        updateAchatRecap();
    }
}

// Fonction pour synchroniser le montant TTC entre onglets
function syncTTCBetweenTabs() {
    const montantTTC = document.getElementById('achat-montant-ttc')?.value;
    if (montantTTC) {
        document.getElementById('achat-montant-ttc-display').value = montantTTC;
        calculateFinancialsFromTTC();
    }
}

// Fonction pour mettre à jour le récapitulatif avec les nouvelles données
function updateAchatRecap() {
    console.log('📋 Mise à jour du récapitulatif achat...');
    
    // Récupérer les valeurs des nouveaux champs
    const fournisseur = document.getElementById('achat-fournisseur')?.selectedOptions[0]?.text || '-';
    const reference = document.getElementById('achat-reference')?.value || '-';
    const categorie = document.getElementById('achat-categorie-pieces')?.selectedOptions[0]?.text || '-';
    const montantTTC = document.getElementById('achat-montant-ttc')?.value || '0,00';
    const dateFacture = document.getElementById('achat-date-facture')?.value || '-';
    
    // Mettre à jour l'affichage
    const elements = {
        'recap-fournisseur': `Fournisseur: ${fournisseur}`,
        'recap-reference': `Référence: ${reference}`,
        'recap-categorie': `Catégorie: ${categorie}`,
        'recap-ttc': `TTC: ${parseFloat(montantTTC).toFixed(2)} €`,
        'recap-date': `Date: ${dateFacture}`
    };
    
    Object.entries(elements).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    });
    
    console.log('✅ Récapitulatif mis à jour:', elements);
}

// Exposer les nouvelles fonctions globalement
// openAchatModal/closeAchatModal déplacés ligne 8258-8259
window.switchAchatTab = switchAchatTab;
window.calculateFinancials = calculateFinancials;
window.updatePaymentStatus = updatePaymentStatus;
window.saveAchat = saveAchat;
window.updateAchatRecap = updateAchatRecap;
window.removeUploadedFile = removeUploadedFile;
window.duplicateCurrentAchat = duplicateCurrentAchat;
window.exportAchatData = exportAchatData;
window.prefillAchatForm = prefillAchatForm;

// Exposer les nouvelles fonctions globalement
window.openPurchaseModal = openPurchaseModal;
window.resetAchatForm = resetAchatForm;
window.loadFournisseursForAchat = loadFournisseursForAchat;
window.loadCategoriesForAchat = loadCategoriesForAchat;
window.loadComptesForAchat = loadComptesForAchat;
window.updateAchatSummary = updateAchatSummary;
window.handleAchatFileSelection = handleAchatFileSelection;
window.validateAchatForm = validateAchatForm;
window.getAchatFormData = getAchatFormData;

console.log('✅ Modal de nouvel achat avec onglets chargé');

// Gestion du drag & drop pour les documents
document.addEventListener('DOMContentLoaded', function() {
    const uploadZone = document.getElementById('upload-zone');
    
    if (uploadZone) {
        uploadZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });
        
        uploadZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
        });
        
        uploadZone.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            handleDocumentUpload(files);
        });
        
        // Clic pour sélectionner des fichiers
        uploadZone.addEventListener('click', function() {
            const fileInput = document.getElementById('achat-documents');
            if (fileInput) {
                fileInput.click();
            }
        });
        
        // Changement de fichiers
        const fileInput = document.getElementById('achat-documents');
        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                handleDocumentUpload(e.target.files);
            });
        }
    }
});

// Exposer les fonctions du modal nouvel achat globalement
window.openAchatModal = openAchatModal;
window.closeAchatModal = closeAchatModal;
window.switchAchatTab = switchAchatTab;
window.calculateFinancials = calculateFinancials;
window.updatePaymentStatus = updatePaymentStatus;
window.saveAchat = saveAchat;
window.updateAchatRecap = updateAchatRecap;
window.removeUploadedFile = removeUploadedFile;
window.duplicateCurrentAchat = duplicateCurrentAchat;
window.exportAchatData = exportAchatData;
window.prefillAchatForm = prefillAchatForm;

// Exposer les nouvelles fonctions pour l'onglet 1 amélioré
window.loadFournisseurs = loadFournisseurs;
window.loadCategories = loadCategories;
window.calculateFinancialsFromTTC = calculateFinancialsFromTTC;
window.syncTTCBetweenTabs = syncTTCBetweenTabs;

/**
 * Charger et afficher la liste des catégories
 */
async function loadCategoriesList() {
    const container = document.getElementById('categories-list');
    const noCategoriesMessage = document.getElementById('no-categories-message');
    
    if (!container) return;
    
    try {
        console.log('📋 Chargement de la liste des catégories...');
        
        // API_BASE est déjà définie globalement
        const response = await fetch(`${API_BASE}/categories.php`);
        
        if (!response.ok) {
            // Essayer de parser la réponse en JSON, sinon extraire le message d'erreur du HTML
            let errorMessage = `Erreur HTTP ${response.status}`;
            try {
                const errorData = await response.json();
                errorMessage = errorData.message || errorData.error || errorMessage;
            } catch (jsonError) {
                // Si le JSON échoue, extraire le message d'erreur du HTML
                const errorText = await response.text();
                if (errorText.includes('<br />')) {
                    const match = errorText.match(/<br\s*\/?><b>(.*?)<\/b>/);
                    if (match) {
                        errorMessage = match[1];
                    } else if (errorText.length < 200) {
                        errorMessage = errorText.replace(/<[^>]*>/g, '').trim();
                    }
                }
            }
            throw new Error(errorMessage);
        }
        
        const data = await response.json();
        
        // Vider le conteneur
        container.innerHTML = '';
        
        if (data.success && Array.isArray(data.data) && data.data.length > 0) {
            // Afficher chaque catégorie
            data.data.forEach(categorie => {
                const categoryCard = createCategoryCard(categorie);
                container.appendChild(categoryCard);
            });
            
            // Masquer le message "aucune catégorie"
            if (noCategoriesMessage) {
                noCategoriesMessage.classList.add('hidden');
            }
            
            console.log(`✅ ${data.data.length} catégories affichées`);
        } else {
            // Aucune catégorie trouvée
            if (noCategoriesMessage) {
                noCategoriesMessage.classList.remove('hidden');
            }
            console.log('⚠️ Aucune catégorie trouvée');
        }
        
    } catch (error) {
        console.error('❌ Erreur chargement catégories:', error);
        
        // Afficher un message de chargement en cours
        container.innerHTML = `
            <div class="col-span-full text-center py-8 text-gray-500">
                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                <p>Chargement des catégories en cours...</p>
            </div>
        `;
        
        // Essayer de recharger automatiquement après 2 secondes
        setTimeout(() => {
            console.log('🔄 Nouvelle tentative de chargement des catégories...');
            loadCategoriesList();
        }, 2000);
    }
}

/**
 * Créer une carte HTML pour une catégorie
 * @param {Object} categorie 
 * @returns {HTMLElement}
 */
function createCategoryCard(categorie) {
    const card = document.createElement('div');
    card.className = 'bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow';
    
    // Extraire l'icône du nom (premier caractère emoji)
    const nomParts = (categorie.nom || '').split(' ');
    const icone = nomParts[0] || '📋';
    const nomAffiche = nomParts.slice(1).join(' ') || categorie.nom || 'Catégorie';
    
    const couleur = categorie.couleur || '#6B7280';
    
    card.innerHTML = `
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                <div class="p-3 rounded-lg" style="background-color: ${couleur}20;">
                    <span class="text-2xl">${icone}</span>
                </div>
                <div class="ml-4">
                    <h4 class="font-semibold text-gray-900">${nomAffiche}</h4>
                    <p class="text-sm text-gray-500">${categorie.code || ''}</p>
                    ${categorie.description ? `<p class="text-xs text-gray-400 mt-1">${categorie.description}</p>` : ''}
                </div>
            </div>
        </div>
        <div class="flex justify-between items-center">
            <div>
                <p class="text-sm text-gray-600">Ordre: ${categorie.ordre_affichage || 0}</p>
                <p class="text-xs text-gray-400">ID: ${categorie.id}</p>
            </div>
            <div class="space-x-2">
                <button onclick="openCategoryModal(${categorie.id})" 
                        class="text-blue-600 hover:text-blue-800 text-sm p-2 hover:bg-blue-50 rounded">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="deleteCategory(${categorie.id})" 
                        class="text-red-600 hover:text-red-800 text-sm p-2 hover:bg-red-50 rounded">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    
    return card;
}

/**
 * Supprimer une catégorie
 * @param {number} categorieId 
 */
async function deleteCategory(categorieId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')) {
        return;
    }
    
    try {
        // API_BASE est déjà définie globalement
        const response = await fetch(`${API_BASE}/categories.php`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: categorieId })
        });
        
        if (!response.ok) {
            // Essayer de parser la réponse en JSON, sinon extraire le message d'erreur du HTML
            let errorMessage = `Erreur HTTP ${response.status}`;
            try {
                const errorData = await response.json();
                errorMessage = errorData.message || errorData.error || errorMessage;
            } catch (jsonError) {
                // Si le JSON échoue, extraire le message d'erreur du HTML
                const errorText = await response.text();
                if (errorText.includes('<br />')) {
                    const match = errorText.match(/<br\s*\/?><b>(.*?)<\/b>/);
                    if (match) {
                        errorMessage = match[1];
                    } else if (errorText.length < 200) {
                        errorMessage = errorText.replace(/<[^>]*>/g, '').trim();
                    }
                }
            }
            throw new Error(errorMessage);
        }
        
        const result = await response.json();
        
        if (result.success) {
            alert('Catégorie supprimée avec succès !');
            // Recharger la liste
            loadCategoriesList();
            
            // Recharger aussi les catégories du modal d'achat si nécessaire
            if (typeof loadCategories === 'function') {
                loadCategories();
            }
        } else {
            alert('Erreur: ' + (result.message || 'Échec de la suppression'));
        }
        
    } catch (error) {
        console.error('❌ Erreur suppression catégorie:', error);
        alert('Erreur lors de la suppression de la catégorie');
    }
}

// Exposer les nouvelles fonctions globalement
window.loadCategoriesList = loadCategoriesList;
window.createCategoryCard = createCategoryCard;
window.deleteCategory = deleteCategory;

// Initialisation des tooltips et des fonctionnalités
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Initialisation du modal achat amélioré...');
    
    // Initialiser les calculs financiers
    calculateFinancials();
    
    // Initialiser les dates par défaut
    const dateInput = document.getElementById('achat-date-facture');
    if (dateInput && !dateInput.value) {
        dateInput.value = new Date().toISOString().split('T')[0];
    }
    
    // Ajouter les event listeners pour les nouveaux champs
    const montantTTC = document.getElementById('achat-montant-ttc');
    if (montantTTC) {
        montantTTC.addEventListener('input', () => {
            calculateFinancialsFromTTC();
            syncTTCBetweenTabs();
        });
    }
    
    const categoriePieces = document.getElementById('achat-categorie-pieces');
    if (categoriePieces) {
        categoriePieces.addEventListener('change', updateAchatRecap);
    }
    
    const reference = document.getElementById('achat-reference');
    if (reference) {
        reference.addEventListener('input', updateAchatRecap);
    }
    
    console.log('✅ Modal achat amélioré initialisé');
});

// ==================== GESTION MODAL CATÉGORIES ====================

/**
 * Ouvrir le modal pour nouvelle catégorie ou modification
 * @param {number|null} categorieId - ID de la catégorie à modifier (null pour nouvelle)
 */
function openCategoryModal(categorieId = null) {
    const modal = document.getElementById('categorieModal');
    const form = document.getElementById('form-categorie');
    const title = document.getElementById('modal-categorie-title');
    
    if (!modal || !form) {
        console.error('❌ Éléments modal catégorie non trouvés');
        return;
    }
    
    // Réinitialiser le formulaire
    form.reset();
    document.getElementById('categorie-id').value = '';
    
    if (categorieId) {
        // ✅ CORRECTION: Validation de l'ID avant modification
        if (!categorieId || categorieId === 'undefined' || categorieId === 'null' || categorieId === '') {
            console.error('❌ ID catégorie invalide:', categorieId);
            alert('Erreur: ID de catégorie invalide');
            return;
        }
        
        // Convertir en entier et valider
        const validId = parseInt(categorieId);
        if (isNaN(validId) || validId <= 0) {
            console.error('❌ ID catégorie doit être un nombre positif:', categorieId);
            alert('Erreur: ID de catégorie doit être un nombre valide');
            return;
        }
        
        // Mode modification
        title.textContent = 'Modifier la Catégorie';
        console.log('🔍 Chargement catégorie ID:', validId);
        main_loadCategoryData(validId);
    } else {
        // Mode création
        title.textContent = 'Nouvelle Catégorie';
        // Valeurs par défaut
        document.getElementById('categorie-ordre').value = '0';
        document.getElementById('categorie-couleur').value = '#10B981';
    }
    
    // Afficher le modal
    modal.classList.add('show');
    
    console.log('📝 Modal catégorie ouvert');
}

/**
 * Fermer le modal des catégories
 */
function closeCategoryModal() {
    const modal = document.getElementById('categorieModal');
    if (modal) {
        modal.classList.remove('show');
        console.log('❌ Modal catégorie fermé');
    }
}

/**
 * Charger les données d'une catégorie pour modification
 * @param {number} categorieId 
 */
// Fonction principale de chargement des données catégorie (utilisée globalement)
async function main_loadCategoryData(categorieId) {
    try {
        // ✅ CORRECTION: Validation de l'ID avant envoi
        if (!categorieId || categorieId === 'undefined' || categorieId === 'null') {
            console.error('❌ ID de catégorie invalide:', categorieId);
            alert('ID de catégorie invalide');
            return;
        }
        
        // Convertir en nombre entier
        const validId = parseInt(categorieId);
        if (isNaN(validId) || validId <= 0) {
            console.error('❌ ID de catégorie doit être un nombre positif:', categorieId);
            alert('ID de catégorie invalide');
            return;
        }
        
        console.log('🔄 Chargement des données de la catégorie ID:', validId, '(raw:', categorieId, ')');
        
        // API_BASE est déjà définie globalement
        const response = await fetch(`${API_BASE}/categories.php?id=${validId}`);
        
        if (!response.ok) {
            // Essayer de parser la réponse en JSON, sinon extraire le message d'erreur du HTML
            let errorMessage = `Erreur HTTP ${response.status}`;
            try {
                const errorData = await response.json();
                errorMessage = errorData.message || errorData.error || errorMessage;
            } catch (jsonError) {
                // Si le JSON échoue, extraire le message d'erreur du HTML
                const errorText = await response.text();
                if (errorText.includes('<br />')) {
                    const match = errorText.match(/<br\s*\/?><b>(.*?)<\/b>/);
                    if (match) {
                        errorMessage = match[1];
                    } else if (errorText.length < 200) {
                        errorMessage = errorText.replace(/<[^>]*>/g, '').trim();
                    }
                }
            }
            throw new Error(errorMessage);
        }
        
        const data = await response.json();
        
        if (data.success && data.data) {
            const categorie = data.data;
            
            // Remplir le formulaire avec tous les champs
            document.getElementById('categorie-id').value = categorie.id;
            document.getElementById('categorie-code').value = categorie.code || '';
            document.getElementById('categorie-nom').value = categorie.nom || '';
            document.getElementById('categorie-description').value = categorie.description || '';
            document.getElementById('categorie-couleur').value = categorie.couleur || '#10B981';
            document.getElementById('categorie-ordre').value = categorie.ordre_affichage || 0;
            
            console.log('✅ Données catégorie chargées complètement:', categorie);
        } else {
            throw new Error('Catégorie non trouvée');
        }
    } catch (error) {
        console.error('❌ Erreur chargement catégorie:', error);
        alert('Erreur lors du chargement de la catégorie: ' + error.message);
    }
}

// Alias pour compatibilité
async function loadCategoryData(categorieId) {
    return main_loadCategoryData(categorieId);
}

/**
 * Sauvegarder une catégorie (création ou modification)
 */
async function saveCategory(event) {
    // ARRÊTER TOUTE SOUMISSION NATURELLE
    event.preventDefault();
    event.stopPropagation();
    event.stopImmediatePropagation();
    
    console.log('🎯 saveCategory() déclenchée - saisie interceptée');
    
    const form = event.target;
    const formData = new FormData(form);
    
    // Validation basique
    const code = formData.get('code')?.trim();
    const nom = formData.get('nom')?.trim();
    
    if (!code) {
        alert('Le code est obligatoire');
        return;
    }
    if (!nom) {
        alert('Le nom est obligatoire');
        return;
    }
    
    console.log('📋 Formulaire validé - code:', code, 'nom:', nom);
    
    // ✅ CORRECTION: Validation de l'ID pour le mode édition
    const categorieIdRaw = formData.get('id');
    let categorieId = null;
    let isEdit = false;
    
    if (categorieIdRaw && categorieIdRaw !== '' && categorieIdRaw !== 'undefined' && categorieIdRaw !== 'null') {
        // Convertir en entier et valider
        const parsedId = parseInt(categorieIdRaw);
        if (!isNaN(parsedId) && parsedId > 0) {
            categorieId = parsedId;
            isEdit = true;
        } else {
            console.error('❌ ID de catégorie invalide:', categorieIdRaw);
            alert('Erreur: ID de catégorie invalide');
            return;
        }
    }
    
    console.log('🔍 Mode édition:', isEdit, 'ID:', categorieId, '(raw:', categorieIdRaw, ')');
    
    const categorieData = {
        code: code.toUpperCase(),
        nom: nom,
        description: formData.get('description') || '',
        icone: formData.get('icone') || 'fas fa-tag',
        couleur: formData.get('couleur') || '#3B82F6',
        actif: formData.get('actif') === 'on', // Checkbox: true si coché, false sinon
        ordre_affichage: parseInt(formData.get('ordre_affichage')) || 0
    };
    
    // ✅ CORRECTION: Ajouter l'ID si en mode édition
    if (isEdit) {
        categorieData.id = categorieId;
    }
    
    console.log('📦 Données à envoyer:', categorieData);
    
    try {
        // API_BASE est déjà définie globalement
        const url = isEdit ? `${API_BASE}/categories.php?id=${categorieId}` : `${API_BASE}/categories.php`;
        const method = isEdit ? 'PUT' : 'POST';
        
        console.log('🌐 URL API:', url, 'Method:', method);
        
        // DÉSACTIVER LE BOUTON POUR ÉVITER LES SOUMISSIONS MULTIPLES
        const submitButton = form.querySelector('button[type="submit"]') || form.querySelector('button[onclick="submitCategoryForm()"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enregistrement...';
        }
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(categorieData)
        });
        
        console.log('📡 Réponse reçue - Status:', response.status, 'OK:', response.ok);
        
        if (!response.ok) {
            // Essayer de parser la réponse en JSON, sinon extraire le message d'erreur du HTML
            let errorMessage = `Erreur HTTP ${response.status}`;
            try {
                const errorData = await response.json();
                errorMessage = errorData.message || errorData.error || errorMessage;
            } catch (jsonError) {
                // Si le JSON échoue, extraire le message d'erreur du HTML
                const errorText = await response.text();
                if (errorText.includes('<br />')) {
                    const match = errorText.match(/<br\s*\/?><b>(.*?)<\/b>/);
                    if (match) {
                        errorMessage = match[1];
                    } else if (errorText.length < 200) {
                        errorMessage = errorText.replace(/<[^>]*>/g, '').trim();
                    }
                }
            }
            console.error('❌ Erreur API:', errorMessage);
            throw new Error(errorMessage);
        }
        
        const result = await response.json();
        console.log('✅ Réponse API:', result);
        
        if (result.success) {
            console.log('🎉 Insertion réussie !');
            alert(isEdit ? 'Catégorie modifiée avec succès !' : 'Catégorie créée avec succès !');
            
            // Fermer le modal
            closeCategoryModal();
            
            // Recharger les catégories
            if (typeof loadCategories === 'function') {
                console.log('🔄 Rechargement des catégories...');
                loadCategories();
            } else {
                console.warn('⚠️ Fonction loadCategories non trouvée');
            }
        } else {
            console.error('❌ Échec de la sauvegarde:', result.message);
            alert('Erreur: ' + (result.message || 'Échec de la sauvegarde'));
        }
        
    } catch (error) {
        console.error('❌ Erreur complète:', error);
        alert('Erreur lors de la sauvegarde: ' + error.message);
    } finally {
        // RÉACTIVER LE BOUTON
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-save mr-2"></i>Enregistrer';
        }
    }
}

// Fonction appelée directement par le bouton "Enregistrer"
function submitCategoryForm() {
    console.log('🎯 submitCategoryForm() déclenchée - bouton direct');
    
    const form = document.getElementById('form-categorie');
    if (!form) {
        console.error('❌ Formulaire form-categorie non trouvé');
        return;
    }
    
    // Utiliser la même logique que saveCategory mais sans event
    const formData = new FormData(form);
    const categorieData = {
        code: formData.get('code')?.trim(),
        nom: formData.get('nom')?.trim(),
        description: formData.get('description') || '',
        icone: formData.get('icone') || 'fas fa-tag',
        couleur: formData.get('couleur') || '#3B82F6',
        actif: formData.get('actif') === 'on',
        ordre_affichage: parseInt(formData.get('ordre_affichage')) || 0
    };
    
    const categorieId = formData.get('id');
    const isEdit = categorieId && categorieId !== '';
    
    // Validation basique
    if (!categorieData.code) {
        alert('Le code est obligatoire');
        return;
    }
    if (!categorieData.nom) {
        alert('Le nom est obligatoire');
        return;
    }
    
    console.log('📋 Données validées:', categorieData);
    
    // Créer un faux event pour réutiliser la logique de saveCategory
    const fakeEvent = {
        target: form,
        preventDefault: () => {},
        stopPropagation: () => {},
        stopImmediatePropagation: () => {}
    };
    
    // Utiliser saveCategory avec le faux event
    return saveCategoryWithData(fakeEvent, categorieData, isEdit);
}

// Fonction séparée pour la logique de sauvegarde avec données pre-formées
async function saveCategoryWithData(event, categorieData, isEdit) {
    const form = event.target;
    
    try {
        // ✅ CORRECTION: Validation de l'ID en mode édition
        if (isEdit) {
            const categorieId = new FormData(form).get('id');
            
            // Validation côté client avant envoi
            if (!categorieId || categorieId === 'undefined' || categorieId === 'null' || categorieId === '') {
                console.error('❌ ID catégorie invalide lors de la sauvegarde:', categorieId);
                throw new Error('ID de catégorie requis et invalide');
            }
            
            const validId = parseInt(categorieId);
            if (isNaN(validId) || validId <= 0) {
                console.error('❌ ID catégorie doit être un nombre positif:', categorieId);
                throw new Error('ID de catégorie doit être un nombre valide');
            }
            
            categorieData.id = validId;
        }
        // NE PAS ajouter d'ID pour une nouvelle catégorie (ajout)
        
        // API_BASE est déjà définie globalement
        const url = isEdit ? `${API_BASE}/categories.php?id=${categorieData.id}` : `${API_BASE}/categories.php`;
        const method = isEdit ? 'PUT' : 'POST';
        
        console.log('🌐 URL API:', url, 'Method:', method, 'Data:', categorieData);
        
        const submitButton = form.querySelector('button[type="button"][onclick="submitCategoryForm()"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enregistrement...';
        }
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(categorieData)
        });
        
        console.log('📡 Réponse reçue - Status:', response.status, 'OK:', response.ok);
        
        if (!response.ok) {
            // Essayer de parser la réponse en JSON, sinon extraire le message d'erreur du HTML
            let errorMessage = `Erreur HTTP ${response.status}`;
            try {
                const errorData = await response.json();
                errorMessage = errorData.message || errorData.error || errorMessage;
            } catch (jsonError) {
                // Si le JSON échoue, extraire le message d'erreur du HTML
                const errorText = await response.text();
                if (errorText.includes('<br />')) {
                    const match = errorText.match(/<br\s*\/?><b>(.*?)<\/b>/);
                    if (match) {
                        errorMessage = match[1];
                    } else if (errorText.length < 200) {
                        errorMessage = errorText.replace(/<[^>]*>/g, '').trim();
                    }
                }
            }
            console.error('❌ Erreur API:', errorMessage);
            throw new Error(errorMessage);
        }
        
        const result = await response.json();
        console.log('✅ Réponse API:', result);
        
        if (result.success) {
            console.log('🎉 Opération réussie !');
            alert(isEdit ? 'Catégorie modifiée avec succès !' : 'Catégorie créée avec succès !');
            
            // Fermer le modal
            closeCategoryModal();
            
            // Recharger les catégories
            if (typeof loadCategories === 'function') {
                console.log('🔄 Rechargement des catégories...');
                loadCategories();
            } else {
                console.warn('⚠️ Fonction loadCategories non trouvée');
            }
        } else {
            console.error('❌ Échec de la sauvegarde:', result.message);
            alert('Erreur: ' + (result.message || 'Échec de la sauvegarde'));
        }
        
    } catch (error) {
        console.error('❌ Erreur complète:', error);
        alert('Erreur lors de la sauvegarde: ' + error.message);
    } finally {
        const submitButton = form.querySelector('button[type="button"][onclick="submitCategoryForm()"]');
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-save mr-2"></i>Enregistrer';
        }
    }
}

// =====================================================
// 📤 EXPORTS GLOBAUX POUR FONCTIONS CRITIQUES
// =====================================================
// Exporter les fonctions essentielles pour éviter les conflits entre fichiers

// API et notifications
window.apiCall = apiCall;
window.showNotification = showNotification || function(message, type = 'info') {
    // Fonction de base si showNotification n'existe pas
    if (type === 'error') {
        alert('Erreur: ' + message);
    } else {
        console.log('Notification:', message);
    }
};

// Fonctions de gestion des catégories
window.loadCategoryData = loadCategoryData;
window.saveCategory = saveCategory;

// Modal et navigation
window.openCategoryModal = openCategoryModal;
window.closeCategoryModal = closeCategoryModal;

// Alias pour compatibilité
window.openEditModal = openCategoryModal;

// Initialiser l'événement de soumission du formulaire (désactivé car on utilise un bouton direct)
document.addEventListener('DOMContentLoaded', function() {
    // Event listener de formulaire désactivé - on utilise maintenant le bouton direct
    console.log('ℹ️ Utilisation du bouton direct pour soumettre le formulaire');
});
