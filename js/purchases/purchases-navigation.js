/**
 * =====================================================
 * 🛒 SECTION: ACHATS ET DÉPENSES - NAVIGATION & DISPLAY
 * =====================================================
 * 
 * Fonctions de navigation entre onglets et affichage
 * pour la section Achats (Dépenses)
 */

/**
 * Affiche un onglet spécifique dans la section Achats
 * @param {string} tabName - Nom de l'onglet à afficher
 */
function showAchatsTab(tabName) {
    console.log(`🔄 Basculement vers l'onglet achats: ${tabName}`);
    
    // Masquer tous les contenus d'onglets
    const allTabContents = document.querySelectorAll('.section .tab-content');
    allTabContents.forEach(content => {
        content.classList.remove('active');
    });
    
    // Désactiver tous les boutons d'onglets
    const allTabButtons = document.querySelectorAll('.section-tab');
    allTabButtons.forEach(button => {
        button.classList.remove('active');
    });
    
    // Afficher le contenu sélectionné
    const targetContent = document.getElementById(`achats-${tabName}-content`);
    if (targetContent) {
        targetContent.classList.add('active');
    }
    
    // Activer le bouton sélectionné
    const activeButton = document.querySelector(`button[onclick="showAchatsTab('${tabName}')"]`);
    if (activeButton) {
        activeButton.classList.add('active');
    }
    
    // Charger le contenu selon l'onglet
    switch (tabName) {
        case 'vue-ensemble':
            loadAchatsVueEnsemble();
            break;
        case 'enregistrements':
            loadAchatsEnregistrements();
            break;
        case 'suivi-paiements':
            loadAchatsSuiviPaiements();
            break;
        case 'categories':
            loadAchatsCategories();
            break;
        case 'rapports':
            loadAchatsRapports();
            break;
    }
}

/**
 * Met à jour l'affichage des achats avec les données actuelles
 */
async function updateAchatsDisplay() {
    console.log('📊 Mise à jour de l\'affichage des achats...');
    
    try {
        // Charger les transactions depuis l'API
        const response = await apiCall('/transactions.php?type=achat');
        if (response.success && response.data) {
            appData.transactions = response.data;
            
            // Mettre à jour les différentes sections
            loadAchatsVueEnsemble();
            loadAchatsEnregistrements();
            
            console.log('✅ Affichage des achats mis à jour');
        }
    } catch (error) {
        console.error('❌ Erreur lors de la mise à jour des achats:', error);
        showNotification('Erreur lors de la mise à jour des données', 'error');
    }
}

/**
 * Initialise les graphiques des achats
 */
function initAchatsCharts() {
    console.log('📈 Initialisation des graphiques des achats...');
    
    // Graphique d'évolution des dépenses
    const ctx = document.getElementById('depensesEvolutionChart');
    if (ctx) {
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct'],
                datasets: [{
                    label: 'Dépenses mensuelles',
                    data: [6500, 7200, 6800, 7500, 8200, 7800, 8100, 8600, 7800, 8547],
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
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + '€';
                            }
                        }
                    }
                }
            }
        });
        
        charts.depensesEvolution = chart;
        console.log('✅ Graphique d\'évolution des dépenses initialisé');
    }
}