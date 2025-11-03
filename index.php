<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de Caisse Régie - Version PHP/PostgreSQL</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <style>
        /* ========== VARIABLES CSS MODERNE ========== */
        :root {
            /* Palette de couleurs raffinée */
            --color-primary: #2563eb;
            --color-primary-dark: #1d4ed8;
            --color-primary-light: #dbeafe;
            --color-secondary: #10b981;
            --color-secondary-dark: #059669;
            --color-secondary-light: #d1fae5;
            --color-accent: #f59e0b;
            --color-accent-light: #fef3c7;
            --color-danger: #ef4444;
            --color-danger-light: #fecaca;
            --color-success: #22c55e;
            --color-warning: #f59e0b;
            --color-info: #3b82f6;
            
            /* Nuances de gris modernes */
            --color-gray-50: #f9fafb;
            --color-gray-100: #f3f4f6;
            --color-gray-200: #e5e7eb;
            --color-gray-300: #d1d5db;
            --color-gray-400: #9ca3af;
            --color-gray-500: #6b7280;
            --color-gray-600: #4b5563;
            --color-gray-700: #374151;
            --color-gray-800: #1f2937;
            --color-gray-900: #111827;
            
            /* Ombres modernes */
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            
            /* Transitions */
            --transition-fast: 0.15s ease-in-out;
            --transition-normal: 0.3s ease-in-out;
            --transition-slow: 0.5s ease-in-out;
            
            /* Border radius */
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
        }

        /* ========== SIDEBAR AMÉLIORÉE ========== */
        .sidebar-active { 
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
            color: white;
            box-shadow: var(--shadow-md);
            transform: translateX(2px);
            border-right: 3px solid var(--color-accent);
        }
        
        .sidebar-item {
            position: relative;
            transition: all var(--transition-normal);
            border-radius: var(--radius-md);
            margin: 0.25rem 0.5rem;
        }
        
        .sidebar-item:hover {
            background: linear-gradient(135deg, var(--color-gray-700) 0%, var(--color-gray-600) 100%);
            transform: translateX(4px);
            box-shadow: var(--shadow-md);
        }
        
        .sidebar-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 0;
            background: var(--color-accent);
            transition: height var(--transition-normal);
            border-radius: 0 2px 2px 0;
        }
        
        .sidebar-item:hover::before,
        .sidebar-active::before {
            height: 60%;
        }

        /* ========== BOUTONS MODERNISÉS ========== */
        .btn-modern {
            position: relative;
            overflow: hidden;
            transition: all var(--transition-normal);
            border: none;
            border-radius: var(--radius-lg);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            box-shadow: var(--shadow-md);
        }
        
        .btn-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left var(--transition-normal);
        }
        
        .btn-modern:hover::before {
            left: 100%;
        }
        
        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-xl);
        }
        
        .btn-modern:active {
            transform: translateY(0);
            box-shadow: var(--shadow-md);
        }
        
        .btn-primary-modern {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
            color: white;
        }
        
        .btn-success-modern {
            background: linear-gradient(135deg, var(--color-secondary) 0%, var(--color-secondary-dark) 100%);
            color: white;
        }
        
        .btn-accent-modern {
            background: linear-gradient(135deg, var(--color-accent) 0%, #d97706 100%);
            color: white;
        }

        /* ========== TABLEAUX INTERACTIFS ========== */
        .table-modern {
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }
        
        .table-modern thead tr {
            background: linear-gradient(135deg, var(--color-gray-50) 0%, var(--color-gray-100) 100%);
        }
        
        .table-modern thead th {
            position: relative;
            padding: 1rem 1.5rem;
            font-weight: 700;
            color: var(--color-gray-700);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 0.75rem;
            border-bottom: 2px solid var(--color-gray-200);
        }
        
        .table-modern tbody tr {
            transition: all var(--transition-fast);
            cursor: pointer;
        }
        
        .table-modern tbody tr:hover {
            background: linear-gradient(135deg, var(--color-gray-50) 0%, #f8fafc 100%);
            transform: scale(1.001);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .table-modern tbody td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--color-gray-100);
            vertical-align: middle;
        }

        /* ========== MODAL NOUVEL ACHAT ========== */

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        /* Priorité pour le modal de catégorie */
        #categorieModal {
            display: none !important;
        }

        #categorieModal.show {
            display: block !important;
        }

        .modal-content {
            background-color: white;
            margin: 2% auto;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            max-width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .large-modal {
            width: 90%;
            max-width: 1200px;
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #dc2626, #991b1b);
            color: white;
            border-radius: 8px 8px 0 0;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
        }

        .modal-close {
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: white;
        }

        .modal-tabs {
            display: flex;
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            overflow-x: auto;
        }

        .tab-btn {
            background: none;
            border: none;
            padding: 15px 20px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
            color: #64748b;
            transition: all 0.3s ease;
        }

        .tab-btn:hover {
            background-color: #f1f5f9;
            color: #374151;
        }

        .tab-btn.active {
            color: #dc2626;
            border-bottom-color: #dc2626;
            background-color: white;
        }

        .tab-content {
            display: none;
            padding: 30px;
            min-height: 400px;
            max-height: calc(90vh - 200px);
            overflow-y: auto;
            overflow-x: hidden;
        }

        .tab-content.active {
            display: block;
        }
        
        .modal-body {
            padding: 0;
            max-height: calc(90vh - 120px);
            overflow: hidden;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #374151;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        .calculation-summary {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #e2e8f0;
        }

        .calc-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }

        .calc-item.total {
            border-top: 2px solid #dc2626;
            margin-top: 10px;
            padding-top: 15px;
            font-weight: bold;
            font-size: 1.1em;
        }

        .summary-box {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #dc2626;
        }

        .summary-box h4 {
            margin: 0 0 15px 0;
            color: #991b1b;
            font-size: 1.1em;
        }
        
        .info-box {
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #0284c7;
        }
        
        .info-box h4 {
            margin: 0 0 10px 0;
            color: #0c4a6e;
            font-size: 1.1em;
        }
        
        .info-box p {
            margin: 0;
            color: #0369a1;
            font-style: italic;
        }

        .summary-content {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .summary-content span {
            color: #374151;
            font-size: 0.95em;
        }

        .upload-zone {
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            background: #f8fafc;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .upload-zone:hover,
        .upload-zone.dragover {
            border-color: #dc2626;
            background: #fef2f2;
        }

        .upload-zone i {
            font-size: 2em;
            color: #9ca3af;
            margin-bottom: 10px;
        }

        .upload-zone p {
            margin: 10px 0;
            color: #6b7280;
        }

        .uploaded-file {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            margin: 8px 0;
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            animation: slideInRight 0.3s ease;
        }

        .uploaded-file-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .uploaded-file-info i {
            color: #0ea5e9;
            font-size: 1.2em;
        }

        .uploaded-file-name {
            font-weight: 500;
            color: #1e293b;
        }

        .uploaded-file-size {
            font-size: 0.85em;
            color: #64748b;
        }

        .remove-file-btn {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
            border-radius: 6px;
            padding: 4px 8px;
            cursor: pointer;
            font-size: 0.8em;
            transition: all 0.2s ease;
        }

        .remove-file-btn:hover {
            background: #fecaca;
            color: #b91c1c;
        }

        /* Styles pour les actions de gestion */
        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border-radius: 6px;
            transition: background-color 0.2s ease;
            cursor: pointer;
        }

        .checkbox-label:hover {
            background: #f8fafc;
        }

        .checkbox-label input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #dc2626;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 12px;
        }

        .action-buttons button {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.9em;
        }

        .action-buttons button:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            transform: translateY(-1px);
        }

        .action-buttons button i {
            font-size: 1em;
        }

        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 5px;
        }

        .checkbox-label input[type="checkbox"] {
            width: auto;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .action-buttons button {
            padding: 10px 20px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .action-buttons button:hover {
            background: #f3f4f6;
            border-color: #dc2626;
            color: #dc2626;
        }

        .modal-footer {
            padding: 20px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            background: #f8fafc;
            border-radius: 0 0 8px 8px;
        }

        /* Footer fixe en bas du modal d'achat */
        .large-modal .modal-body {
            max-height: calc(90vh - 200px);
            overflow-y: auto;
            padding-bottom: 0;
        }

        .large-modal .sticky-footer {
            position: sticky;
            bottom: 0;
            background: #f8fafc;
            border-top: 1px solid #e5e7eb;
            padding: 20px;
            margin-top: 0;
            z-index: 10;
            border-radius: 0;
            flex-shrink: 0;
        }

        .large-modal .sticky-footer .flex {
            width: 100%;
        }

        .btn-large {
            padding: 12px 32px;
            font-size: 16px;
            font-weight: 600;
            min-width: 200px;
        }

        /* Améliorer l'espacement pour le contenu scrollable */
        .large-modal .tab-content {
            padding-bottom: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: #dc2626;
            color: white;
        }

        .btn-primary:hover {
            background: #b91c1c;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        .form-help {
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #6b7280;
        }

        /* Styles pour le modal de catégorie */
        #categorieModal .modal-content {
            max-width: 800px !important;
            margin: 0.2% auto !important;
            max-height: 95vh !important;
            overflow-y: auto !important;
        }

        #categorieModal .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-bottom: 0.5rem !important;
        }
        
        #categorieModal .form-group {
            margin-bottom: 0.5rem !important;
        }
        
        #categorieModal label {
            margin-bottom: 0.25rem !important;
            font-size: 0.9rem !important;
        }
        
        #categorieModal .form-control {
            padding: 0.5rem !important;
            font-size: 0.9rem !important;
        }
        
        #categorieModal textarea.form-control {
            padding: 0.5rem !important;
            font-size: 0.9rem !important;
            resize: none !important;
        }
        
        /* Optimisation du contenu du modal catégorie */
        #categorieModal .modal-body {
            max-height: calc(95vh - 100px);
            overflow-y: auto;
        }
        
        #categorieModal .bg-gray-50,
        #categorieModal [style*="background: #f8fafc"] {
            margin-bottom: 0.5rem !important;
            padding: 0.5rem !important;
        }
        
        #categorieModal .modal-footer {
            position: sticky;
            bottom: 0;
            background: white;
            border-top: 1px solid #e5e7eb;
            margin-top: 0.25rem !important;
            padding: 0.75rem !important;
        }

        #categorieModal .form-group input[type="color"] {
            height: 40px;
            padding: 2px;
            cursor: pointer;
        }

        /* Responsive pour le modal de catégorie */
        @media (max-width: 768px) {
            #categorieModal .modal-content {
                margin: 0.25% auto !important;
                width: 98% !important;
                max-height: 96vh !important;
            }
            
            #categorieModal .form-row {
                grid-template-columns: 1fr !important;
            }
            
            #categorieModal .p-6 {
                padding: 0.5rem !important;
            }
            
            #categorieModal .space-y-6 > * + * {
                margin-top: 0.5rem !important;
            }
            
            #categorieModal .rounded-lg {
                padding: 0.5rem !important;
            }
            
            #categorieModal h5 {
                margin-bottom: 0.5rem !important;
                font-size: 0.9rem !important;
            }
            
            #categorieModal .modal-header {
                padding: 0.75rem !important;
            }
            
            #categorieModal .modal-footer {
                padding: 0.5rem !important;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .modal-tabs {
                flex-wrap: nowrap;
                overflow-x: auto;
                white-space: nowrap;
            }
            
            .tab-btn {
                flex-shrink: 0;
                min-width: 140px;
                text-align: center;
                padding: 12px 16px;
                font-size: 0.9rem;
            }
            
            .tab-btn i {
                margin-right: 4px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .modal-content.large-modal {
                margin: 2% auto;
                width: 98%;
                max-height: 96vh;
            }
            
            .modal-body {
                padding: 20px 15px;
                overflow-y: auto;
            }
            
            .modal-header {
                padding: 15px;
            }
            
            .modal-header h2 {
                font-size: 1.2rem;
            }
            
            .form-group label {
                font-size: 0.9rem;
            }
            
            .calculation-summary {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .summary-box {
                padding: 15px;
            }
            
            .upload-zone {
                padding: 20px 10px;
            }
        }
        
        @media (max-width: 480px) {
            .modal-tabs {
                font-size: 0.8rem;
            }
            
            .tab-btn {
                min-width: 120px;
                padding: 10px 12px;
            }
            
            .modal-content.large-modal {
                margin: 1% auto;
                width: 99%;
            }
        }
        
        .table-modern tbody tr:last-child td {
            border-bottom: none;
        }
        
        /* Headers avec dégradés spécialisés */
        .table-clients thead tr {
            background: linear-gradient(135deg, var(--color-primary-light) 0%, #dbeafe 50%, #eff6ff 100%);
        }
        
        .table-fournisseurs thead tr {
            background: linear-gradient(135deg, var(--color-secondary-light) 0%, #d1fae5 50%, #ecfdf5 100%);
        }

        /* ========== CARTES DE MÉTRIQUES ÉLÉGANTES ========== */
        .metric-card {
            background: white;
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            box-shadow: var(--shadow-lg);
            transition: all var(--transition-normal);
            border: 1px solid var(--color-gray-100);
            position: relative;
            overflow: hidden;
        }
        
        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--color-primary), var(--color-secondary), var(--color-accent));
        }
        
        .metric-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
            border-color: var(--color-primary);
        }
        
        .metric-card .metric-icon {
            width: 3rem;
            height: 3rem;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--color-primary-light), var(--color-primary));
            color: var(--color-primary-dark);
            box-shadow: var(--shadow-md);
        }
        
        .metric-card .metric-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--color-gray-900);
            line-height: 1;
            margin-bottom: 0.25rem;
        }
        
        .metric-card .metric-label {
            color: var(--color-gray-600);
            font-size: 0.875rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* ========== FILTRES MODERNISÉS ========== */
        .filter-container {
            background: white;
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--color-gray-100);
            transition: all var(--transition-normal);
        }
        
        .filter-container:hover {
            box-shadow: var(--shadow-xl);
            border-color: var(--color-primary);
        }
        
        .filter-input {
            border: 2px solid var(--color-gray-200);
            border-radius: var(--radius-lg);
            padding: 0.75rem 1rem;
            transition: all var(--transition-normal);
            background: var(--color-gray-50);
        }
        
        .filter-input:focus {
            border-color: var(--color-primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }
        
        .filter-input:hover {
            border-color: var(--color-gray-300);
            background: white;
        }

        /* ========== MODALES ÉLÉGANTES ========== */
        /* Suppression des styles génériques qui interfèrent avec le modal achat */
        /* .modal { display: none; } */ /* Remarqué - géré par styles spécifiques du modal achat */
        /* .modal-content { } */       /* Remarqué - géré par styles spécifiques du modal achat */
        
        .modal-body { 
            padding: 1.5rem; 
        }

        /* ========== INDICATEURS DE STATUT ========== */
        .status-indicator {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .status-success {
            background: var(--color-secondary-light);
            color: var(--color-secondary-dark);
        }
        
        .status-warning {
            background: var(--color-accent-light);
            color: #d97706;
        }
        
        .status-danger {
            background: var(--color-danger-light);
            color: var(--color-danger);
        }
        
        .status-info {
            background: var(--color-primary-light);
            color: var(--color-primary-dark);
        }

        /* ========== ANIMATIONS ========== */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideInDown {
            from { 
                opacity: 0;
                transform: translateY(-30px) scale(0.95);
            }
            to { 
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes slideOutRight {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(30px);
            }
        }
        
        /* ========== LOADING AMÉLIORÉ ========== */
        .loading {
            display: inline-block;
            width: 24px;
            height: 24px;
            border: 3px solid var(--color-gray-200);
            border-top: 3px solid var(--color-primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        .loading-text {
            animation: pulse 2s infinite;
        }

        /* ========== CHART CONTAINER ========== */
        .chart-container { 
            height: 400px;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }

        /* ========== RESPONSIVE PRINT ========== */
        @media print { 
            .no-print { display: none !important; } 
            body { margin: 0; padding: 0; }
            .print-full-width { width: 100% !important; margin: 0 !important; }
        }

        /* ========== ONGLETS TIERS MODERNISÉS ========== */
        .tiers-tab-content {
            min-height: 300px;
            border-radius: var(--radius-lg);
        }
        
        .tiers-tab {
            transition: all var(--transition-normal);
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
            position: relative;
            overflow: hidden;
        }
        
        .tiers-tab::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--color-primary);
            transform: scaleX(0);
            transition: transform var(--transition-normal);
        }
        
        .tiers-tab.active {
            border-color: var(--color-primary) !important;
            color: var(--color-primary) !important;
            background: linear-gradient(135deg, var(--color-primary-light), white);
            font-weight: 600;
        }
        
        .tiers-tab.active::before {
            transform: scaleX(1);
        }

        /* ========== SYSTÈME DE FILTRES AVANCÉS AVEC ICÔNES ========== */
        .advanced-filters-container {
            background: linear-gradient(135deg, white 0%, #f8fafc 100%);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--color-gray-100);
            margin-bottom: 1.5rem;
            transition: all var(--transition-normal);
        }

        .filters-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--color-gray-200);
        }

        .filters-header h5 {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--color-gray-800);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Zone de recherche globale améliorée */
        .global-search-container {
            position: relative;
            flex: 1;
            max-width: 400px;
        }

        .global-search-input {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 3rem;
            border: 2px solid var(--color-gray-200);
            border-radius: var(--radius-lg);
            background: white;
            font-size: 0.875rem;
            transition: all var(--transition-normal);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .global-search-input:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1), 0 1px 3px rgba(0, 0, 0, 0.1);
            outline: none;
        }

        .global-search-input:hover:not(:focus) {
            border-color: var(--color-gray-300);
        }

        .global-search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--color-gray-400);
            font-size: 1rem;
            transition: color var(--transition-fast);
        }

        .global-search-input:focus + .global-search-icon {
            color: var(--color-primary);
        }

        /* Icônes de filtres cliquables */
        .filter-icons-container {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .filter-icon-btn {
            position: relative;
            width: 3rem;
            height: 3rem;
            border-radius: var(--radius-lg);
            border: 2px solid var(--color-gray-200);
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all var(--transition-normal);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .filter-icon-btn:hover {
            border-color: var(--color-primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
        }

        .filter-icon-btn.active {
            border-color: var(--color-primary);
            background: linear-gradient(135deg, var(--color-primary), var(--color-primary-dark));
            color: white;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .filter-icon-btn .badge {
            position: absolute;
            top: -0.5rem;
            right: -0.5rem;
            width: 1.25rem;
            height: 1.25rem;
            background: var(--color-accent);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.625rem;
            font-weight: 700;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        /* Panneaux de filtres déroulants */
        .filter-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            z-index: 50;
            min-width: 320px;
            background: white;
            border-radius: var(--radius-xl);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border: 1px solid var(--color-gray-200);
            padding: 1.5rem;
            margin-top: 0.5rem;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all var(--transition-normal);
        }

        .filter-dropdown.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .filter-dropdown::before {
            content: '';
            position: absolute;
            top: -8px;
            right: 2rem;
            width: 16px;
            height: 16px;
            background: white;
            border: 1px solid var(--color-gray-200);
            border-bottom: none;
            border-right: none;
            transform: rotate(45deg);
        }

        .filter-section {
            margin-bottom: 1.5rem;
        }

        .filter-section:last-child {
            margin-bottom: 0;
        }

        .filter-section h6 {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--color-gray-700);
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .filter-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 0.5rem;
        }

        .filter-option {
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--color-gray-200);
            border-radius: var(--radius-md);
            background: var(--color-gray-50);
            font-size: 0.8rem;
            cursor: pointer;
            transition: all var(--transition-fast);
            text-align: center;
        }

        .filter-option:hover {
            border-color: var(--color-primary);
            background: var(--color-primary-light);
        }

        .filter-option.selected {
            border-color: var(--color-primary);
            background: var(--color-primary);
            color: white;
        }

        /* Indicateur de filtres actifs */
        .active-filters-bar {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--color-gray-200);
        }

        .active-filter-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.375rem 0.75rem;
            background: var(--color-primary-light);
            color: var(--color-primary-dark);
            border-radius: var(--radius-md);
            font-size: 0.75rem;
            font-weight: 500;
            border: 1px solid var(--color-primary);
        }

        .active-filter-tag button {
            width: 1rem;
            height: 1rem;
            border-radius: 50%;
            background: var(--color-primary);
            color: white;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.625rem;
            transition: all var(--transition-fast);
        }

        .active-filter-tag button:hover {
            background: var(--color-primary-dark);
        }

        .clear-all-filters {
            padding: 0.375rem 0.75rem;
            background: var(--color-danger-light);
            color: var(--color-danger);
            border: 1px solid var(--color-danger);
            border-radius: var(--radius-md);
            font-size: 0.75rem;
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .clear-all-filters:hover {
            background: var(--color-danger);
            color: white;
        }

        /* Animations pour les microinteractions */
        @keyframes filterPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .filter-icon-btn.pulse {
            animation: filterPulse 0.3s ease-in-out;
        }

        /* Responsive pour les filtres */
        @media (max-width: 768px) {
            .filters-header {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }

            .global-search-container {
                max-width: none;
            }

            .filter-icons-container {
                justify-content: center;
            }

            .filter-dropdown {
                left: 0;
                right: 0;
                min-width: auto;
            }

            .filter-dropdown::before {
                left: 50%;
                right: auto;
                transform: translateX(-50%) rotate(45deg);
            }
        }

        /* ========== SYSTÈME D'ONGLETS BANQUE/CAISSE ========== */
        .section-tabs {
            display: flex;
            border-bottom: 2px solid var(--color-gray-200);
            margin-bottom: 1.5rem;
            background: white;
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }
        
        .section-tab {
            flex: 1;
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, var(--color-gray-50), var(--color-gray-100));
            border: none;
            cursor: pointer;
            transition: all var(--transition-normal);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            position: relative;
            overflow: hidden;
        }
        
        .section-tab::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--color-primary), var(--color-accent));
            transform: scaleX(0);
            transition: transform var(--transition-normal);
        }
        
        .section-tab:hover {
            background: linear-gradient(135deg, var(--color-gray-100), var(--color-gray-200));
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .section-tab.active {
            background: linear-gradient(135deg, white, var(--color-gray-50));
            color: var(--color-primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        .section-tab.active::before {
            transform: scaleX(1);
        }
        
        /* Onglets spécialisés pour Banque */
        .banque-tab.active {
            color: var(--color-info);
        }
        
        .banque-tab.active::before {
            background: linear-gradient(90deg, var(--color-info), var(--color-primary));
        }
        
        /* Onglets spécialisés pour Caisse */
        .caisse-tab.active {
            color: var(--color-accent);
        }
        
        .caisse-tab.active::before {
            background: linear-gradient(90deg, var(--color-accent), #d97706);
        }
        
        /* Contenu des onglets */
        .tab-content {
            display: none;
            animation: fadeIn var(--transition-normal);
        }
        
        .tab-content.active {
            display: block;
        }

        /* ========== MICRO-ANIMATIONS SPÉCIALES ========== */
        .hover-lift {
            transition: all var(--transition-normal);
        }
        
        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        .hover-scale {
            transition: all var(--transition-fast);
        }
        
        .hover-scale:hover {
            transform: scale(1.02);
        }
        
        /* ========== BADGE MODERNE ========== */
        .badge-modern {
            display: inline-flex;
            align-items: center;
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            box-shadow: var(--shadow-sm);
        }

        /* ========== SCROLLBAR MODERNE ========== */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--color-gray-100);
            border-radius: var(--radius-sm);
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--color-gray-400), var(--color-gray-500));
            border-radius: var(--radius-sm);
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, var(--color-gray-500), var(--color-gray-600));
        }

        /* ========== CORRECTION BOUTONS CAISSE ========== */
        /* Forcer la visibilité des boutons de caisse en mode modification */
        #caisse-validate-button {
            display: inline-block !important;
            visibility: visible !important;
            opacity: 1 !important;
            position: relative !important;
            z-index: 9999 !important;
            background-color: #f97316 !important;
            color: white !important;
            border: none !important;
            pointer-events: auto !important;
        }

        #caisse-modal-buttons {
            display: flex !important;
            justify-content: flex-end !important;
            gap: 0.5rem !important;
        }

        #caisse-modal-buttons button {
            display: inline-block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        /* ========== STYLES ONGLETS TRANSACTION ========== */
        .transaction-tab {
            transition: all var(--transition-normal);
            position: relative;
            cursor: pointer;
        }
        
        .transaction-tab:hover {
            color: var(--color-primary) !important;
        }
        
        .transaction-tab-content {
            animation: fadeIn 0.3s ease-in-out;
        }
        
        /* ========== STYLES ONGLETS VIREMENT ========== */
        .transfer-tab {
            transition: all var(--transition-normal);
            position: relative;
            cursor: pointer;
        }
        
        .transfer-tab:hover {
            color: #9333ea !important; /* purple-600 */
        }
        
        .transfer-tab-content {
            animation: fadeIn 0.3s ease-in-out;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* ========== CARTES MODERNES POUR CATÉGORIES ========== */
        .category-card-modern {
            position: relative;
            background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(249,250,251,0.9) 100%);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            overflow: hidden;
        }
        
        .category-card-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--color-primary), var(--color-secondary));
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .category-card-modern:hover::before {
            opacity: 1;
        }
        
        .category-icon-container {
            background: linear-gradient(135deg, rgba(255,255,255,0.8) 0%, rgba(248,250,252,0.8) 100%);
            border: 2px solid rgba(255,255,255,0.3);
            box-shadow: 
                0 4px 6px -1px rgba(0, 0, 0, 0.1),
                0 2px 4px -1px rgba(0, 0, 0, 0.06),
                inset 0 1px 0 rgba(255,255,255,0.1);
        }
        
        .action-btn {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 8px;
            font-size: 14px;
        }
        
        .action-btn:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-1px);
        }
        
        .card-shine {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
        }
        
        /* Grid responsive pour les cartes */
        #categories-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            padding: 1rem;
        }
        
        @media (max-width: 768px) {
            #categories-list {
                grid-template-columns: 1fr;
                gap: 1rem;
                padding: 0.5rem;
            }
            
            .category-card-modern {
                padding: 1rem !important;
            }
            
            .category-icon-container {
                padding: 0.75rem !important;
            }
            
            .category-card-modern h4 {
                font-size: 1rem !important;
            }
        }
        
        @media (max-width: 480px) {
            #categories-list {
                padding: 0.25rem;
            }
            
            .category-card-modern {
                margin: 0.5rem 0;
            }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-800 text-white flex-shrink-0 no-print">
            <div class="p-6 border-b border-gray-700">
                <h1 class="text-xl font-bold"><i class="fas fa-cash-register mr-2"></i>Caisse Régie</h1>
                <p class="text-xs text-gray-400 mt-1">Version PHP/PostgreSQL</p>
            </div>
            <nav class="mt-6">
                <a href="#" onclick="showSection('dashboard')" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white sidebar-item sidebar-active">
                    <i class="fas fa-tachometer-alt mr-3"></i>Tableau de bord
                </a>
                <a href="#" onclick="showSection('transactions')" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white sidebar-item">
                    <i class="fas fa-exchange-alt mr-3"></i>Transactions
                </a>
                <a href="#" onclick="showSection('clients')" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white sidebar-item">
                    <i class="fas fa-user-friends mr-3"></i>Clients
                </a>
                <a href="#" onclick="showSection('fournisseurs')" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white sidebar-item">
                    <i class="fas fa-truck mr-3"></i>Fournisseurs
                </a>
                <a href="#" onclick="showSection('banque')" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white sidebar-item">
                    <i class="fas fa-university mr-3"></i>Banque
                </a>
                <a href="#" onclick="showSection('caisse')" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white sidebar-item">
                    <i class="fas fa-cash-register mr-3"></i>Caisse
                </a>
                <a href="#" onclick="showSection('achats')" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white sidebar-item">
                    <i class="fas fa-shopping-cart mr-3"></i>Achats (Dépenses)
                </a>
                <a href="#" onclick="showSection('rapports')" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white sidebar-item">
                    <i class="fas fa-chart-bar mr-3"></i>Rapports
                </a>
                <a href="#" onclick="showSection('parametres')" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white sidebar-item">
                    <i class="fas fa-cog mr-3"></i>Paramètres
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <header class="bg-white shadow-sm p-4 flex justify-between items-center no-print">
                <h2 id="page-title" class="text-2xl font-semibold text-gray-800">Tableau de bord</h2>
                <div class="flex gap-2">
                    <div id="connection-status" class="px-3 py-1 rounded-full text-sm">
                        <i class="fas fa-circle mr-1"></i>
                        <span id="status-text">Connexion...</span>
                    </div>
                    <button onclick="refreshData()" class="btn-modern btn-primary-modern px-4 py-2">
                        <i class="fas fa-sync-alt mr-2"></i>Actualiser
                    </button>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 p-6 overflow-auto print-full-width">
                <!-- Dashboard Section -->
                <div id="dashboard-section" class="section">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <div class="bg-white p-6 rounded-lg shadow">
                            <div class="flex items-center">
                                <div class="p-2 bg-blue-100 rounded-lg">
                                    <i class="fas fa-wallet text-blue-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Solde Total</p>
                                    <p id="total-balance" class="text-2xl font-semibold">
                                        <div class="loading"></div>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow">
                            <div class="flex items-center">
                                <div class="p-2 bg-green-100 rounded-lg">
                                    <i class="fas fa-arrow-up text-green-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Total Recettes</p>
                                    <p id="total-recettes" class="text-2xl font-semibold text-green-600">
                                        <div class="loading"></div>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow">
                            <div class="flex items-center">
                                <div class="p-2 bg-red-100 rounded-lg">
                                    <i class="fas fa-arrow-down text-red-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Total Dépenses</p>
                                    <p id="total-depenses" class="text-2xl font-semibold text-red-600">
                                        <div class="loading"></div>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow">
                            <div class="flex items-center">
                                <div class="p-2 bg-yellow-100 rounded-lg">
                                    <i class="fas fa-list text-yellow-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Transactions</p>
                                    <p id="total-transactions" class="text-2xl font-semibold">
                                        <div class="loading"></div>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-white p-6 rounded-lg shadow">
                            <h3 class="text-lg font-semibold mb-4">Répartition Recettes/Dépenses</h3>
                            <div class="chart-container">
                                <canvas id="repartitionChart"></canvas>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow">
                            <h3 class="text-lg font-semibold mb-4">Répartition par Compte</h3>
                            <div class="chart-container">
                                <canvas id="comptesChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow mt-6">
                        <h3 class="text-lg font-semibold mb-4">Dernières Transactions</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full table-auto">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="px-4 py-2 text-left">Date</th>
                                        <th class="px-4 py-2 text-left">Type</th>
                                        <th class="px-4 py-2 text-left">Description</th>
                                        <th class="px-4 py-2 text-left">Compte</th>
                                        <th class="px-4 py-2 text-left">Tiers</th>
                                        <th class="px-4 py-2 text-right">Montant</th>
                                        <th class="px-4 py-2 text-center no-print">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="recent-transactions">
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="loading mx-auto"></div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Transactions Section -->
                <div id="transactions-section" class="section" style="display: none;">
                    <div class="flex flex-wrap justify-between items-center mb-6 gap-2">
                        <h3 class="text-xl font-semibold">Gestion des Transactions</h3>
                        <div class="flex gap-2">
                            <button onclick="openTransactionModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg no-print">
                                <i class="fas fa-plus mr-2"></i>Nouvelle Transaction
                            </button>
                            <button onclick="openTransferModal()" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg no-print">
                                <i class="fas fa-exchange-alt mr-2"></i>Virement de fonds
                            </button>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow mb-6 no-print">
                        <h4 class="text-lg font-semibold mb-4">Filtres</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                            <input type="text" id="search-transactions" placeholder="Rechercher..." class="border rounded-lg px-3 py-2" oninput="debouncedApplyTransactionFilters()">
                            <select id="filter-type" class="border rounded-lg px-3 py-2" onchange="applyTransactionFilters()">
                                <option value="">Tous les types</option>
                                <option value="recette">Recettes</option>
                                <option value="depense">Dépenses</option>
                                <option value="virement_debit">Virements (débit)</option>
                                <option value="virement_credit">Virements (crédit)</option>
                            </select>
                            <select id="filter-account" class="border rounded-lg px-3 py-2" onchange="applyTransactionFilters()">
                                <option value="">Tous les comptes</option>
                            </select>
                            <select id="filter-tiers" class="border rounded-lg px-3 py-2" onchange="applyTransactionFilters()">
                                <option value="">Tous les tiers</option>
                            </select>
                            <select id="filter-category" class="border rounded-lg px-3 py-2" onchange="applyTransactionFilters()">
                                <option value="">Toutes les catégories</option>
                            </select>
                            <select id="filter-month" class="border rounded-lg px-3 py-2" onchange="applyTransactionFilters()">
                                <option value="">Tous les mois</option>
                                <option value="1">Janvier</option>
                                <option value="2">Février</option>
                                <option value="3">Mars</option>
                                <option value="4">Avril</option>
                                <option value="5">Mai</option>
                                <option value="6">Juin</option>
                                <option value="7">Juillet</option>
                                <option value="8">Août</option>
                                <option value="9">Septembre</option>
                                <option value="10">Octobre</option>
                                <option value="11">Novembre</option>
                                <option value="12">Décembre</option>
                            </select>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow">
                        <div class="overflow-x-auto">
                            <table class="min-w-full table-auto">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="px-4 py-3 text-left">Date</th>
                                        <th class="px-4 py-3 text-left">Type</th>
                                        <th class="px-4 py-3 text-left">Description</th>
                                        <th class="px-4 py-3 text-left">Compte</th>
                                        <th class="px-4 py-3 text-left">Tiers</th>
                                        <th class="px-4 py-3 text-left">Catégorie</th>
                                        <th class="px-4 py-3 text-right">Montant</th>
                                        <th class="px-4 py-3 text-right">Solde</th>
                                        <th class="px-4 py-3 text-center no-print">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="transactions-table">
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="loading mx-auto"></div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Autres sections similaires à l'original mais adaptées pour PHP... -->
                <!-- Section Clients -->
                <div id="clients-section" class="section" style="display: none;">
                    <!-- Filtres Clients -->
                    <div class="filter-container mb-6 no-print">
                        <h4 class="text-lg font-semibold mb-4">
                            <i class="fas fa-filter mr-2 text-blue-600"></i>Filtres Clients
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                            <input type="text" id="search-clients" placeholder="Rechercher clients (nom, code, contact, email, téléphone...)" 
                                   class="filter-input col-span-1 lg:col-span-2" 
                                   oninput="debouncedApplyClientsFilters()">
                            
                            <select id="filter-clients-solde" class="filter-input" onchange="applyClientsFilters()">
                                <option value="">Tous les soldes</option>
                                <option value="debiteur">Débiteurs (solde > 0)</option>
                                <option value="crediteur">Créditeurs (solde < 0)</option>
                                <option value="equilibre">Équilibrés (solde = 0)</option>
                            </select>
                            
                            <select id="filter-clients-periode" class="filter-input" onchange="applyClientsFilters()">
                                <option value="">Toutes les périodes</option>
                                <option value="recent">Récents (30 derniers jours)</option>
                                <option value="mois">Ce mois</option>
                                <option value="trimestre">Ce trimestre</option>
                                <option value="ancien">Plus anciens</option>
                            </select>
                            
                            <select id="filter-clients-statut" class="filter-input" onchange="applyClientsFilters()">
                                <option value="">Tous les statuts</option>
                                <option value="actif">Actifs</option>
                                <option value="inactif">Inactifs</option>
                            </select>
                        </div>
                        
                        <!-- Statistiques des filtres clients -->
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                                <span id="clients-count-total" class="flex items-center">
                                    <i class="fas fa-user-friends mr-1 text-blue-600"></i>
                                    Total clients: <strong class="ml-1">0</strong>
                                </span>
                                <span id="clients-count-filtered" class="flex items-center">
                                    <i class="fas fa-filter mr-1 text-green-600"></i>
                                    Affichés: <strong class="ml-1">0</strong>
                                </span>
                                <span id="clients-solde-total" class="flex items-center">
                                    <i class="fas fa-euro-sign mr-1 text-purple-600"></i>
                                    Solde total: <strong class="ml-1">0,00 €</strong>
                                </span>
                                <button onclick="clearClientsFilters()" class="text-blue-600 hover:text-blue-800 ml-auto">
                                    <i class="fas fa-times mr-1"></i>Effacer les filtres
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Contenu Clients -->
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-2xl font-bold text-gray-800">
                            <i class="fas fa-user-friends mr-3 text-blue-600"></i>Gestion des Clients
                        </h3>
                        <button onclick="openTiersModal('client')" class="btn-modern btn-primary-modern px-6 py-3 no-print">
                            <i class="fas fa-plus mr-2"></i>Nouveau Client
                        </button>
                    </div>
                    <div class="hover-lift">
                        <div class="overflow-x-auto">
                            <table class="table-modern table-clients min-w-full">
                                <thead>
                                    <tr>
                                        <th class="text-left">Code</th>
                                        <th class="text-left">Raison Sociale</th>
                                        <th class="text-left">Contact</th>
                                        <th class="text-left">Téléphone</th>
                                        <th class="text-left">Email</th>
                                        <th class="text-right">Solde</th>
                                        <th class="text-center no-print">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="clients-table">
                                    <tr>
                                        <td colspan="7" class="text-center py-8">
                                            <div class="loading mx-auto"></div>
                                            <p class="text-gray-500 mt-3 loading-text">Chargement des clients...</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Section Fournisseurs -->
                <div id="fournisseurs-section" class="section" style="display: none;">
                    <!-- Filtres Fournisseurs -->
                    <div class="filter-container mb-6 no-print">
                        <h4 class="text-lg font-semibold mb-4">
                            <i class="fas fa-filter mr-2 text-green-600"></i>Filtres Fournisseurs
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                            <input type="text" id="search-fournisseurs" placeholder="Rechercher fournisseurs (nom, code, contact, email, téléphone...)" 
                                   class="filter-input col-span-1 lg:col-span-2" 
                                   oninput="debouncedApplyFournisseursFilters()">
                            
                            <select id="filter-fournisseurs-solde" class="filter-input" onchange="applyFournisseursFilters()">
                                <option value="">Tous les soldes</option>
                                <option value="debiteur">Débiteurs (solde > 0)</option>
                                <option value="crediteur">Créditeurs (solde < 0)</option>
                                <option value="equilibre">Équilibrés (solde = 0)</option>
                            </select>
                            
                            <select id="filter-fournisseurs-periode" class="filter-input" onchange="applyFournisseursFilters()">
                                <option value="">Toutes les périodes</option>
                                <option value="recent">Récents (30 derniers jours)</option>
                                <option value="mois">Ce mois</option>
                                <option value="trimestre">Ce trimestre</option>
                                <option value="ancien">Plus anciens</option>
                            </select>
                            
                            <select id="filter-fournisseurs-statut" class="filter-input" onchange="applyFournisseursFilters()">
                                <option value="">Tous les statuts</option>
                                <option value="actif">Actifs</option>
                                <option value="inactif">Inactifs</option>
                            </select>
                        </div>
                        
                        <!-- Statistiques des filtres fournisseurs -->
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                                <span id="fournisseurs-count-total" class="flex items-center">
                                    <i class="fas fa-truck mr-1 text-green-600"></i>
                                    Total fournisseurs: <strong class="ml-1">0</strong>
                                </span>
                                <span id="fournisseurs-count-filtered" class="flex items-center">
                                    <i class="fas fa-filter mr-1 text-green-600"></i>
                                    Affichés: <strong class="ml-1">0</strong>
                                </span>
                                <span id="fournisseurs-solde-total" class="flex items-center">
                                    <i class="fas fa-euro-sign mr-1 text-purple-600"></i>
                                    Solde total: <strong class="ml-1">0,00 €</strong>
                                </span>
                                <button onclick="clearFournisseursFilters()" class="text-green-600 hover:text-green-800 ml-auto">
                                    <i class="fas fa-times mr-1"></i>Effacer les filtres
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Contenu Fournisseurs -->
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-2xl font-bold text-gray-800">
                            <i class="fas fa-truck mr-3 text-green-600"></i>Gestion des Fournisseurs
                        </h3>
                        <button onclick="openTiersModal('fournisseur')" class="btn-modern btn-success-modern px-6 py-3 no-print">
                            <i class="fas fa-plus mr-2"></i>Nouveau Fournisseur
                        </button>
                    </div>
                    <div class="hover-lift">
                        <div class="overflow-x-auto">
                            <table class="table-modern table-fournisseurs min-w-full">
                                <thead>
                                    <tr>
                                        <th class="text-left">Code</th>
                                        <th class="text-left">Raison Sociale</th>
                                        <th class="text-left">Contact</th>
                                        <th class="text-left">Téléphone</th>
                                        <th class="text-left">Email</th>
                                        <th class="text-right">Solde</th>
                                        <th class="text-center no-print">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="fournisseurs-table">
                                    <tr>
                                        <td colspan="7" class="text-center py-8">
                                            <div class="loading mx-auto"></div>
                                            <p class="text-gray-500 mt-3 loading-text">Chargement des fournisseurs...</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Section Banque avec Onglets -->
                <div id="banque-section" class="section" style="display: none;">
                    <!-- Header de la section -->
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-2xl font-bold text-gray-800">
                            <i class="fas fa-university mr-3 text-blue-600"></i>Gestion Bancaire
                        </h3>
                        <button onclick="openAccountModal('banque')" class="btn-modern btn-primary-modern px-6 py-3 no-print">
                            <i class="fas fa-plus mr-2"></i>Nouveau Compte Bancaire
                        </button>
                    </div>

                    <!-- Système d'onglets Banque -->
                    <div class="section-tabs">
                        <button class="section-tab banque-tab active" onclick="showBanqueTab('comptes')">
                            <i class="fas fa-credit-card mr-2"></i>Comptes
                        </button>
                        <button class="section-tab banque-tab" onclick="showBanqueTab('historique')">
                            <i class="fas fa-history mr-2"></i>Historique
                        </button>
                        <button class="section-tab banque-tab" onclick="showBanqueTab('virements')">
                            <i class="fas fa-exchange-alt mr-2"></i>Virements
                        </button>
                        <button class="section-tab banque-tab" onclick="showBanqueTab('rapports')">
                            <i class="fas fa-chart-line mr-2"></i>Rapports
                        </button>
                    </div>

                    <!-- Contenu Onglet Comptes -->
                    <div id="banque-comptes-content" class="tab-content active">
                        <div class="filter-container mb-6 no-print">
                            <h4 class="text-lg font-semibold mb-4">
                                <i class="fas fa-filter mr-2 text-blue-600"></i>Filtres Comptes Bancaires
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <input type="text" placeholder="Rechercher compte..." class="filter-input">
                                <select class="filter-input">
                                    <option>Tous les types</option>
                                    <option>Compte courant</option>
                                    <option>Livret</option>
                                    <option>Épargne</option>
                                </select>
                                <select class="filter-input">
                                    <option>Toutes les banques</option>
                                    <option>BNP Paribas</option>
                                    <option>Crédit Agricole</option>
                                    <option>Société Générale</option>
                                </select>
                                <button class="btn-modern btn-primary-modern px-4 py-2">
                                    <i class="fas fa-search mr-2"></i>Filtrer
                                </button>
                            </div>
                        </div>

                        <div id="banque-cards" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                            <!-- Cards seront chargées ici -->
                            <div class="metric-card hover-lift">
                                <div class="metric-icon" style="background: linear-gradient(135deg, #dbeafe, #3b82f6); color: #1d4ed8;">
                                    <i class="fas fa-university text-xl"></i>
                                </div>
                                <div class="metric-value">€15,234</div>
                                <div class="metric-label">Compte Principal</div>
                                <div class="mt-2 text-sm text-gray-600">BNP Paribas - ****1234</div>
                            </div>
                        </div>
                    </div>

                    <!-- Contenu Onglet Historique -->
                    <div id="banque-historique-content" class="tab-content">
                        <div class="filter-container mb-6 no-print">
                            <h4 class="text-lg font-semibold mb-4">
                                <i class="fas fa-filter mr-2 text-blue-600"></i>Filtres Historique
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                                <input type="date" placeholder="Date début" class="filter-input">
                                <input type="date" placeholder="Date fin" class="filter-input">
                                <select class="filter-input">
                                    <option>Tous les comptes</option>
                                    <option>Compte Principal</option>
                                    <option>Compte Épargne</option>
                                </select>
                                <select class="filter-input">
                                    <option>Tous les types</option>
                                    <option>Dépôts</option>
                                    <option>Retraits</option>
                                    <option>Virements</option>
                                </select>
                                <button class="btn-modern btn-primary-modern px-4 py-2">
                                    <i class="fas fa-search mr-2"></i>Rechercher
                                </button>
                            </div>
                        </div>

                        <div class="hover-lift">
                            <div class="overflow-x-auto">
                                <table class="table-modern min-w-full">
                                    <thead>
                                        <tr style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 50%, #93c5fd 100%);">
                                            <th class="text-left">Date</th>
                                            <th class="text-left">Compte</th>
                                            <th class="text-left">Description</th>
                                            <th class="text-left">Type</th>
                                            <th class="text-right">Montant</th>
                                            <th class="text-right">Solde</th>
                                            <th class="text-center no-print">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="banque-transactions">
                                        <tr>
                                            <td colspan="7" class="text-center py-8">
                                                <div class="loading mx-auto"></div>
                                                <p class="text-gray-500 mt-3 loading-text">Chargement de l'historique bancaire...</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Contenu Onglet Virements -->
                    <div id="banque-virements-content" class="tab-content">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div class="filter-container">
                                <h4 class="text-lg font-semibold mb-4">
                                    <i class="fas fa-paper-plane mr-2 text-blue-600"></i>Nouveau Virement
                                </h4>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Compte débiteur</label>
                                        <select class="filter-input w-full">
                                            <option>Sélectionner un compte</option>
                                            <option>Compte Principal - ****1234</option>
                                            <option>Compte Épargne - ****5678</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Compte créditeur</label>
                                        <input type="text" placeholder="IBAN du destinataire" class="filter-input w-full">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Montant</label>
                                        <input type="number" placeholder="0,00 €" class="filter-input w-full">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Motif</label>
                                        <input type="text" placeholder="Motif du virement" class="filter-input w-full">
                                    </div>
                                    <button class="btn-modern btn-primary-modern px-6 py-3 w-full">
                                        <i class="fas fa-paper-plane mr-2"></i>Effectuer le Virement
                                    </button>
                                </div>
                            </div>

                            <div class="filter-container">
                                <h4 class="text-lg font-semibold mb-4">
                                    <i class="fas fa-list mr-2 text-blue-600"></i>Virements Récents
                                </h4>
                                <div class="space-y-3">
                                    <div class="p-3 bg-gray-50 rounded-lg">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <p class="font-semibold">Virement SEPA</p>
                                                <p class="text-sm text-gray-600">Vers ****9876 - 15/10/2025</p>
                                            </div>
                                            <span class="text-lg font-bold text-red-600">-€1,250</span>
                                        </div>
                                    </div>
                                    <div class="p-3 bg-gray-50 rounded-lg">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <p class="font-semibold">Virement Interne</p>
                                                <p class="text-sm text-gray-600">Vers Épargne - 14/10/2025</p>
                                            </div>
                                            <span class="text-lg font-bold text-blue-600">-€500</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contenu Onglet Rapports -->
                    <div id="banque-rapports-content" class="tab-content">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div class="filter-container">
                                <h4 class="text-lg font-semibold mb-4">
                                    <i class="fas fa-chart-bar mr-2 text-blue-600"></i>Générer un Rapport Bancaire
                                </h4>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Type de rapport</label>
                                        <select class="filter-input w-full">
                                            <option>Relevé de compte</option>
                                            <option>Synthèse mensuelle</option>
                                            <option>Analyse des flux</option>
                                            <option>Rapport annuel</option>
                                        </select>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Date début</label>
                                            <input type="date" class="filter-input w-full">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Date fin</label>
                                            <input type="date" class="filter-input w-full">
                                        </div>
                                    </div>
                                    <button class="btn-modern btn-primary-modern px-6 py-3 w-full">
                                        <i class="fas fa-file-alt mr-2"></i>Générer le Rapport
                                    </button>
                                </div>
                            </div>

                            <div class="filter-container">
                                <h4 class="text-lg font-semibold mb-4">
                                    <i class="fas fa-chart-pie mr-2 text-blue-600"></i>Statistiques Bancaires
                                </h4>
                                <div class="space-y-4">
                                    <div class="flex justify-between p-3 bg-blue-50 rounded-lg">
                                        <span class="font-medium">Solde Total</span>
                                        <span class="font-bold text-blue-600">€18,742.50</span>
                                    </div>
                                    <div class="flex justify-between p-3 bg-green-50 rounded-lg">
                                        <span class="font-medium">Entrées du mois</span>
                                        <span class="font-bold text-green-600">€5,230.00</span>
                                    </div>
                                    <div class="flex justify-between p-3 bg-red-50 rounded-lg">
                                        <span class="font-medium">Sorties du mois</span>
                                        <span class="font-bold text-red-600">€3,450.00</span>
                                    </div>
                                    <div class="flex justify-between p-3 bg-yellow-50 rounded-lg">
                                        <span class="font-medium">Virements en attente</span>
                                        <span class="font-bold text-yellow-600">2</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Caisse avec Onglets -->
                <div id="caisse-section" class="section" style="display: none;">
                    <!-- Header simplifié de la section -->
                    <div class="mb-4">
                        <h3 class="text-2xl font-bold text-gray-800">
                            <i class="fas fa-cash-register mr-3 text-orange-600"></i>Gestion de Caisse
                        </h3>
                    </div>

                    <!-- Système d'onglets Caisse avec boutons de contrôle -->
                    <div class="flex justify-between items-center mb-4">
                        <div class="section-tabs">
                            <button class="section-tab caisse-tab active" onclick="showCaisseTab('caisses')">
                                <i class="fas fa-cash-register mr-2"></i>Caisses
                            </button>
                            <button class="section-tab caisse-tab" onclick="showCaisseTab('historique')">
                                <i class="fas fa-history mr-2"></i>Historique
                            </button>
                            <button class="section-tab caisse-tab" onclick="showCaisseTab('mouvements')">
                                <i class="fas fa-arrows-alt-v mr-2"></i>Mouvements
                            </button>
                            <button class="section-tab caisse-tab" onclick="showCaisseTab('rapports')">
                                <i class="fas fa-chart-line mr-2"></i>Rapports
                            </button>
                        </div>
                        
                        <!-- Boutons de contrôle -->
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center space-x-2 bg-green-50 px-3 py-2 rounded-lg border border-green-200">
                                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                <span class="text-sm font-medium text-green-700">Connecté</span>
                            </div>
                            <button class="btn-modern bg-gradient-to-r from-blue-500 to-blue-600 text-white px-4 py-2 hover:from-blue-600 hover:to-blue-700 transition-all duration-200">
                                <i class="fas fa-sync-alt mr-2"></i>Actualiser
                            </button>
                        </div>
                    </div>

                    <!-- Contenu Onglet Caisses -->
                    <div id="caisse-caisses-content" class="tab-content active">
                        <!-- Header avec actions et recherche globale améliorée -->
                        <div class="advanced-filters-container">
                            <!-- En-tête principal -->
                            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start mb-6">
                                <div class="mb-4 lg:mb-0 flex-1">
                                    <h4 class="text-2xl font-bold text-gray-800 mb-2">
                                        <i class="fas fa-th-large mr-3 text-orange-600"></i>Vue d'ensemble des Caisses
                                    </h4>
                                    <p class="text-gray-600 text-lg">Gérez et surveillez l'état de toutes vos caisses en temps réel</p>
                                </div>
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <button onclick="openAccountModal('caisse')" class="btn-modern btn-accent-modern px-8 py-4 hover-lift text-lg">
                                        <i class="fas fa-plus mr-3"></i>Nouvelle Caisse
                                    </button>
                                    <button class="btn-modern bg-gradient-to-r from-blue-500 to-blue-600 text-white px-8 py-4 hover:from-blue-600 hover:to-blue-700 hover-lift text-lg">
                                        <i class="fas fa-download mr-3"></i>Exporter
                                    </button>
                                </div>
                            </div>

                            <!-- Système de filtres avancés avec recherche globale -->
                            <div class="filters-header">
                                <h5>
                                    <i class="fas fa-search text-orange-600"></i>
                                    Recherche & Filtres Intelligents
                                </h5>
                                
                                <!-- Zone de recherche globale améliorée -->
                                <div class="global-search-container">
                                    <input type="text" 
                                           placeholder="Rechercher dans toutes les données des caisses..." 
                                           class="global-search-input"
                                           id="globalCaisseSearch"
                                           oninput="performGlobalCaisseSearch(this.value)">
                                    <i class="fas fa-search global-search-icon"></i>
                                </div>

                                <!-- Icônes de filtres cliquables -->
                                <div class="filter-icons-container">
                                    <div class="relative">
                                        <button class="filter-icon-btn" id="typeFilterBtn" onclick="toggleFilterDropdown('typeFilter')">
                                            <i class="fas fa-tags"></i>
                                            <span class="badge" id="typeFilterCount" style="display: none;">0</span>
                                        </button>
                                        
                                        <!-- Dropdown Type -->
                                        <div class="filter-dropdown" id="typeFilterDropdown">
                                            <div class="filter-section">
                                                <h6>Type de Caisse</h6>
                                                <div class="filter-group">
                                                    <div class="filter-option" onclick="toggleFilterOption(this, 'type', 'principale')">
                                                        <i class="fas fa-cash-register mr-1 text-orange-500"></i>Principale
                                                    </div>
                                                    <div class="filter-option" onclick="toggleFilterOption(this, 'type', 'secondaire')">
                                                        <i class="fas fa-store mr-1 text-blue-500"></i>Secondaire
                                                    </div>
                                                    <div class="filter-option" onclick="toggleFilterOption(this, 'type', 'petite')">
                                                        <i class="fas fa-coins mr-1 text-amber-500"></i>Petite Caisse
                                                    </div>
                                                    <div class="filter-option" onclick="toggleFilterOption(this, 'type', 'mobile')">
                                                        <i class="fas fa-mobile-alt mr-1 text-red-500"></i>Mobile
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="relative">
                                        <button class="filter-icon-btn" id="statusFilterBtn" onclick="toggleFilterDropdown('statusFilter')">
                                            <i class="fas fa-circle"></i>
                                            <span class="badge" id="statusFilterCount" style="display: none;">0</span>
                                        </button>
                                        
                                        <!-- Dropdown Statut -->
                                        <div class="filter-dropdown" id="statusFilterDropdown">
                                            <div class="filter-section">
                                                <h6>Statut des Caisses</h6>
                                                <div class="filter-group">
                                                    <div class="filter-option" onclick="toggleFilterOption(this, 'status', 'ouverte')">
                                                        <span class="w-2 h-2 bg-green-500 rounded-full inline-block mr-1"></span>Ouverte
                                                    </div>
                                                    <div class="filter-option" onclick="toggleFilterOption(this, 'status', 'fermee')">
                                                        <span class="w-2 h-2 bg-red-500 rounded-full inline-block mr-1"></span>Fermée
                                                    </div>
                                                    <div class="filter-option" onclick="toggleFilterOption(this, 'status', 'maintenance')">
                                                        <span class="w-2 h-2 bg-yellow-500 rounded-full inline-block mr-1"></span>Maintenance
                                                    </div>
                                                    <div class="filter-option" onclick="toggleFilterOption(this, 'status', 'suspendue')">
                                                        <span class="w-2 h-2 bg-gray-500 rounded-full inline-block mr-1"></span>Suspendue
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="relative">
                                        <button class="filter-icon-btn" id="amountFilterBtn" onclick="toggleFilterDropdown('amountFilter')">
                                            <i class="fas fa-euro-sign"></i>
                                            <span class="badge" id="amountFilterCount" style="display: none;">0</span>
                                        </button>
                                        
                                        <!-- Dropdown Montant -->
                                        <div class="filter-dropdown" id="amountFilterDropdown">
                                            <div class="filter-section">
                                                <h6>Plage de Soldes</h6>
                                                <div class="filter-group">
                                                    <div class="filter-option" onclick="toggleFilterOption(this, 'amount', 'low')">
                                                        <i class="fas fa-arrow-down mr-1 text-red-500"></i>< €500
                                                    </div>
                                                    <div class="filter-option" onclick="toggleFilterOption(this, 'amount', 'medium')">
                                                        <i class="fas fa-minus mr-1 text-yellow-500"></i>€500-€2000
                                                    </div>
                                                    <div class="filter-option" onclick="toggleFilterOption(this, 'amount', 'high')">
                                                        <i class="fas fa-arrow-up mr-1 text-green-500"></i>> €2000
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="relative">
                                        <button class="filter-icon-btn" id="activityFilterBtn" onclick="toggleFilterDropdown('activityFilter')">
                                            <i class="fas fa-chart-line"></i>
                                            <span class="badge" id="activityFilterCount" style="display: none;">0</span>
                                        </button>
                                        
                                        <!-- Dropdown Activité -->
                                        <div class="filter-dropdown" id="activityFilterDropdown">
                                            <div class="filter-section">
                                                <h6>Niveau d'Activité</h6>
                                                <div class="filter-group">
                                                    <div class="filter-option" onclick="toggleFilterOption(this, 'activity', 'high')">
                                                        <i class="fas fa-fire mr-1 text-red-500"></i>Très Active
                                                    </div>
                                                    <div class="filter-option" onclick="toggleFilterOption(this, 'activity', 'medium')">
                                                        <i class="fas fa-chart-bar mr-1 text-yellow-500"></i>Modérée
                                                    </div>
                                                    <div class="filter-option" onclick="toggleFilterOption(this, 'activity', 'low')">
                                                        <i class="fas fa-bed mr-1 text-blue-500"></i>Faible
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Barre des filtres actifs -->
                            <div class="active-filters-bar" id="activeFiltersBar" style="display: none;">
                                <span class="text-sm font-medium text-gray-600">Filtres actifs:</span>
                                <div id="activeFilterTags"></div>
                                <button class="clear-all-filters" onclick="clearAllFilters()">
                                    <i class="fas fa-times mr-1"></i>Tout effacer
                                </button>
                            </div>
                        </div>

                        <!-- Tableau de bord des statistiques amélioré -->
                        <div class="mb-8">
                            <div class="flex items-center justify-between mb-6">
                                <h5 class="text-xl font-bold text-gray-800">
                                    <i class="fas fa-chart-pie mr-2 text-orange-600"></i>Tableau de Bord - Vue d'ensemble
                                </h5>
                                <div class="flex items-center space-x-2 text-sm text-gray-500">
                                    <i class="fas fa-sync-alt"></i>
                                    <span>Mis à jour il y a 2 min</span>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                <!-- Total Caisses -->
                                <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="bg-white bg-opacity-25 p-3 rounded-xl">
                                            <i class="fas fa-cash-register text-2xl"></i>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-orange-200 text-xs font-semibold uppercase tracking-wider">Total</div>
                                            <div class="text-3xl font-black">3</div>
                                        </div>
                                    </div>
                                    <div class="border-t border-orange-400 pt-3">
                                        <p class="text-orange-100 text-sm font-medium">Caisses enregistrées</p>
                                        <p class="text-xs text-orange-200 mt-1">+1 ce mois</p>
                                    </div>
                                </div>

                                <!-- Caisses Ouvertes -->
                                <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="bg-white bg-opacity-25 p-3 rounded-xl">
                                            <i class="fas fa-unlock text-2xl"></i>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-green-200 text-xs font-semibold uppercase tracking-wider">Actives</div>
                                            <div class="text-3xl font-black">2</div>
                                        </div>
                                    </div>
                                    <div class="border-t border-green-400 pt-3">
                                        <p class="text-green-100 text-sm font-medium">Caisses ouvertes</p>
                                        <div class="flex items-center justify-between text-xs text-green-200 mt-1">
                                            <span>Taux d'activité</span>
                                            <span class="font-semibold">67%</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Solde Total -->
                                <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="bg-white bg-opacity-25 p-3 rounded-xl">
                                            <i class="fas fa-euro-sign text-2xl"></i>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-blue-200 text-xs font-semibold uppercase tracking-wider">Solde</div>
                                            <div class="text-3xl font-black">3,389</div>
                                        </div>
                                    </div>
                                    <div class="border-t border-blue-400 pt-3">
                                        <p class="text-blue-100 text-sm font-medium">Total en caisse (€)</p>
                                        <div class="flex items-center text-xs text-blue-200 mt-1">
                                            <i class="fas fa-arrow-up mr-1"></i>
                                            <span>+5.2% vs hier</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mouvements du jour -->
                                <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="bg-white bg-opacity-25 p-3 rounded-xl">
                                            <i class="fas fa-exchange-alt text-2xl"></i>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-purple-200 text-xs font-semibold uppercase tracking-wider">Aujourd'hui</div>
                                            <div class="text-3xl font-black">12</div>
                                        </div>
                                    </div>
                                    <div class="border-t border-purple-400 pt-3">
                                        <p class="text-purple-100 text-sm font-medium">Opérations effectuées</p>
                                        <div class="flex items-center justify-between text-xs text-purple-200 mt-1">
                                            <span>Dernière: 09:45</span>
                                            <span class="font-semibold">↗ +2 vs hier</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Indicateurs additionnels -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                                <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas fa-clock text-yellow-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Dernière reconciliation</p>
                                            <p class="font-semibold text-gray-800">Il y a 3 heures</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas fa-shield-alt text-indigo-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Sécurité système</p>
                                            <p class="font-semibold text-green-600">Optimal</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-pink-100 rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas fa-users text-pink-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Utilisateurs connectés</p>
                                            <p class="font-semibold text-gray-800">3 opérateurs</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gestion des caisses - Interface améliorée -->
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-6">
                                <h5 class="text-xl font-bold text-gray-800">
                                    <i class="fas fa-store mr-2 text-orange-600"></i>Gestion des Caisses Détaillée
                                </h5>
                                <div class="flex items-center space-x-3">
                                    <button class="text-sm bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded-lg transition-colors">
                                        <i class="fas fa-th mr-1"></i>Vue grille
                                    </button>
                                    <button class="text-sm bg-blue-500 text-white px-3 py-1 rounded-lg">
                                        <i class="fas fa-list mr-1"></i>Vue liste
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-8">
                            <!-- Caisse Principale - Version améliorée -->
                            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 relative overflow-hidden">
                                <!-- Indicateur de performance -->
                                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-orange-500 to-orange-600"></div>
                                
                                <!-- Header avec statut -->
                                <div class="flex items-start justify-between mb-6">
                                    <div class="flex items-center">
                                        <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl flex items-center justify-center text-white mr-4 shadow-lg">
                                            <i class="fas fa-cash-register text-2xl"></i>
                                        </div>
                                        <div>
                                            <h5 class="text-xl font-bold text-gray-800">Caisse Principale</h5>
                                            <p class="text-sm text-gray-500 font-medium">REF: CP-001</p>
                                            <p class="text-xs text-gray-400 mt-1">Ouverte depuis 08:30</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800 border border-green-200">
                                            <span class="w-3 h-3 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                                            Active
                                        </span>
                                        <p class="text-xs text-gray-500 mt-2">Responsable: Marie D.</p>
                                    </div>
                                </div>
                                
                                <!-- Solde principal -->
                                <div class="mb-6 p-4 bg-gradient-to-r from-orange-50 to-orange-100 rounded-xl border border-orange-200">
                                    <div class="flex justify-between items-center mb-3">
                                        <span class="text-sm font-semibold text-orange-700">Solde actuel</span>
                                        <span class="text-3xl font-black text-orange-600">€2,847.30</span>
                                    </div>
                                    <div class="bg-orange-200 rounded-full h-3 mb-2">
                                        <div class="bg-gradient-to-r from-orange-500 to-orange-600 h-3 rounded-full shadow-inner" style="width: 84%"></div>
                                    </div>
                                    <div class="flex justify-between text-xs text-orange-600 font-medium">
                                        <span>Min: €0</span>
                                        <span class="font-bold">84% de capacité</span>
                                        <span>Max: €3,400</span>
                                    </div>
                                </div>

                                <!-- Métriques journalières -->
                                <div class="grid grid-cols-3 gap-3 mb-6">
                                    <div class="text-center p-3 bg-green-50 rounded-xl border border-green-100">
                                        <div class="text-lg font-bold text-green-600">+€234</div>
                                        <div class="text-xs text-green-600 font-medium">Entrées (5)</div>
                                    </div>
                                    <div class="text-center p-3 bg-red-50 rounded-xl border border-red-100">
                                        <div class="text-lg font-bold text-red-600">-€67</div>
                                        <div class="text-xs text-red-600 font-medium">Sorties (2)</div>
                                    </div>
                                    <div class="text-center p-3 bg-blue-50 rounded-xl border border-blue-100">
                                        <div class="text-lg font-bold text-blue-600">7</div>
                                        <div class="text-xs text-blue-600 font-medium">Opérations</div>
                                    </div>
                                </div>

                                <!-- Informations additionnelles -->
                                <div class="mb-6 space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Dernière opération:</span>
                                        <span class="font-medium text-gray-800">09:45 - Vente</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Prochaine réconciliation:</span>
                                        <span class="font-medium text-gray-800">18:00</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Niveau de sécurité:</span>
                                        <span class="font-medium text-green-600">
                                            <i class="fas fa-shield-alt mr-1"></i>Élevé
                                        </span>
                                    </div>
                                </div>

                                <!-- Actions améliorées -->
                                <div class="flex gap-2">
                                    <button class="flex-1 bg-gradient-to-r from-orange-500 to-orange-600 text-white px-4 py-3 rounded-xl text-sm font-semibold hover:from-orange-600 hover:to-orange-700 transition-all duration-200 shadow-md">
                                        <i class="fas fa-eye mr-2"></i>Détails
                                    </button>
                                    <button class="flex-1 bg-gray-100 text-gray-700 px-4 py-3 rounded-xl text-sm font-semibold hover:bg-gray-200 transition-colors">
                                        <i class="fas fa-edit mr-2"></i>Configurer
                                    </button>
                                    <button class="px-4 py-3 bg-blue-100 text-blue-600 rounded-xl hover:bg-blue-200 transition-colors">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Petite Caisse - Version améliorée -->
                            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 relative overflow-hidden">
                                <!-- Indicateur de performance -->
                                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-amber-600"></div>
                                
                                <!-- Header avec statut -->
                                <div class="flex items-start justify-between mb-6">
                                    <div class="flex items-center">
                                        <div class="w-16 h-16 bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl flex items-center justify-center text-white mr-4 shadow-lg">
                                            <i class="fas fa-coins text-2xl"></i>
                                        </div>
                                        <div>
                                            <h5 class="text-xl font-bold text-gray-800">Petite Caisse</h5>
                                            <p class="text-sm text-gray-500 font-medium">REF: PC-002</p>
                                            <p class="text-xs text-gray-400 mt-1">Ouverte depuis 09:15</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800 border border-green-200">
                                            <span class="w-3 h-3 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                                            Active
                                        </span>
                                        <p class="text-xs text-gray-500 mt-2">Responsable: Jean L.</p>
                                    </div>
                                </div>
                                
                                <!-- Solde principal -->
                                <div class="mb-6 p-4 bg-gradient-to-r from-amber-50 to-amber-100 rounded-xl border border-amber-200">
                                    <div class="flex justify-between items-center mb-3">
                                        <span class="text-sm font-semibold text-amber-700">Solde actuel</span>
                                        <span class="text-3xl font-black text-amber-600">€542.11</span>
                                    </div>
                                    <div class="bg-amber-200 rounded-full h-3 mb-2">
                                        <div class="bg-gradient-to-r from-amber-500 to-amber-600 h-3 rounded-full shadow-inner" style="width: 54%"></div>
                                    </div>
                                    <div class="flex justify-between text-xs text-amber-600 font-medium">
                                        <span>Min: €0</span>
                                        <span class="font-bold">54% de capacité</span>
                                        <span>Max: €1,000</span>
                                    </div>
                                </div>

                                <!-- Métriques journalières -->
                                <div class="grid grid-cols-3 gap-3 mb-6">
                                    <div class="text-center p-3 bg-green-50 rounded-xl border border-green-100">
                                        <div class="text-lg font-bold text-green-600">+€89</div>
                                        <div class="text-xs text-green-600 font-medium">Entrées (3)</div>
                                    </div>
                                    <div class="text-center p-3 bg-red-50 rounded-xl border border-red-100">
                                        <div class="text-lg font-bold text-red-600">-€25</div>
                                        <div class="text-xs text-red-600 font-medium">Sorties (1)</div>
                                    </div>
                                    <div class="text-center p-3 bg-blue-50 rounded-xl border border-blue-100">
                                        <div class="text-lg font-bold text-blue-600">4</div>
                                        <div class="text-xs text-blue-600 font-medium">Opérations</div>
                                    </div>
                                </div>

                                <!-- Informations additionnelles -->
                                <div class="mb-6 space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Dernière opération:</span>
                                        <span class="font-medium text-gray-800">14:22 - Achat</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Prochaine réconciliation:</span>
                                        <span class="font-medium text-gray-800">16:00</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Niveau de sécurité:</span>
                                        <span class="font-medium text-green-600">
                                            <i class="fas fa-shield-alt mr-1"></i>Standard
                                        </span>
                                    </div>
                                </div>

                                <!-- Actions améliorées -->
                                <div class="flex gap-2">
                                    <button class="flex-1 bg-gradient-to-r from-amber-500 to-amber-600 text-white px-4 py-3 rounded-xl text-sm font-semibold hover:from-amber-600 hover:to-amber-700 transition-all duration-200 shadow-md">
                                        <i class="fas fa-eye mr-2"></i>Détails
                                    </button>
                                    <button class="flex-1 bg-gray-100 text-gray-700 px-4 py-3 rounded-xl text-sm font-semibold hover:bg-gray-200 transition-colors">
                                        <i class="fas fa-edit mr-2"></i>Configurer
                                    </button>
                                    <button class="px-4 py-3 bg-blue-100 text-blue-600 rounded-xl hover:bg-blue-200 transition-colors">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Caisse Mobile - Version améliorée -->
                            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 relative overflow-hidden opacity-75">
                                <!-- Indicateur de performance -->
                                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-gray-400 to-gray-500"></div>
                                
                                <!-- Header avec statut -->
                                <div class="flex items-start justify-between mb-6">
                                    <div class="flex items-center">
                                        <div class="w-16 h-16 bg-gradient-to-br from-gray-400 to-gray-500 rounded-2xl flex items-center justify-center text-white mr-4 shadow-lg">
                                            <i class="fas fa-mobile-alt text-2xl"></i>
                                        </div>
                                        <div>
                                            <h5 class="text-xl font-bold text-gray-800">Caisse Mobile</h5>
                                            <p class="text-sm text-gray-500 font-medium">REF: CM-003</p>
                                            <p class="text-xs text-gray-400 mt-1">Fermée depuis hier</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800 border border-red-200">
                                            <span class="w-3 h-3 bg-red-500 rounded-full mr-2"></span>
                                            Fermée
                                        </span>
                                        <p class="text-xs text-gray-500 mt-2">Non assignée</p>
                                    </div>
                                </div>
                                
                                <!-- Solde principal -->
                                <div class="mb-6 p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl border border-gray-200">
                                    <div class="flex justify-between items-center mb-3">
                                        <span class="text-sm font-semibold text-gray-700">Solde actuel</span>
                                        <span class="text-3xl font-black text-gray-400">€0.00</span>
                                    </div>
                                    <div class="bg-gray-200 rounded-full h-3 mb-2">
                                        <div class="bg-gray-300 h-3 rounded-full shadow-inner" style="width: 0%"></div>
                                    </div>
                                    <div class="flex justify-between text-xs text-gray-500 font-medium">
                                        <span>Min: €0</span>
                                        <span class="font-bold">0% de capacité</span>
                                        <span>Max: €500</span>
                                    </div>
                                </div>

                                <!-- Métriques journalières -->
                                <div class="grid grid-cols-3 gap-3 mb-6">
                                    <div class="text-center p-3 bg-gray-50 rounded-xl border border-gray-100">
                                        <div class="text-lg font-bold text-gray-400">€0</div>
                                        <div class="text-xs text-gray-400 font-medium">Entrées (0)</div>
                                    </div>
                                    <div class="text-center p-3 bg-gray-50 rounded-xl border border-gray-100">
                                        <div class="text-lg font-bold text-gray-400">€0</div>
                                        <div class="text-xs text-gray-400 font-medium">Sorties (0)</div>
                                    </div>
                                    <div class="text-center p-3 bg-gray-50 rounded-xl border border-gray-100">
                                        <div class="text-lg font-bold text-gray-400">0</div>
                                        <div class="text-xs text-gray-400 font-medium">Opérations</div>
                                    </div>
                                </div>

                                <!-- Informations additionnelles -->
                                <div class="mb-6 space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Dernière opération:</span>
                                        <span class="font-medium text-gray-400">Aucune</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Disponible pour:</span>
                                        <span class="font-medium text-gray-800">Ouverture</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Statut technique:</span>
                                        <span class="font-medium text-green-600">
                                            <i class="fas fa-check-circle mr-1"></i>Prêt
                                        </span>
                                    </div>
                                </div>

                                <!-- Actions améliorées -->
                                <div class="flex gap-2">
                                    <button class="flex-1 bg-gradient-to-r from-green-500 to-green-600 text-white px-4 py-3 rounded-xl text-sm font-semibold hover:from-green-600 hover:to-green-700 transition-all duration-200 shadow-md">
                                        <i class="fas fa-unlock mr-2"></i>Ouvrir
                                    </button>
                                    <button class="flex-1 bg-gray-100 text-gray-700 px-4 py-3 rounded-xl text-sm font-semibold hover:bg-gray-200 transition-colors">
                                        <i class="fas fa-edit mr-2"></i>Configurer
                                    </button>
                                    <button class="px-4 py-3 bg-blue-100 text-blue-600 rounded-xl hover:bg-blue-200 transition-colors">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contenu Onglet Historique -->
                    <div id="caisse-historique-content" class="tab-content">
                        <div class="flex flex-wrap justify-between items-center mb-6 gap-2">
                            <h3 class="text-xl font-semibold">Historique des Opérations de Caisse</h3>
                            <div class="flex gap-2">
                                <button onclick="openCaisseOperationModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg no-print">
                                    <i class="fas fa-plus mr-2"></i>Nouvelle Opération
                                </button>
                                <button onclick="openCaisseTransferModal()" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg no-print">
                                    <i class="fas fa-exchange-alt mr-2"></i>Transfert entre comptes
                                </button>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-lg shadow mb-6 no-print">
                            <h4 class="text-lg font-semibold mb-4">Filtres</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                                <input type="text" id="search-caisse-historique" placeholder="Rechercher..." class="border rounded-lg px-3 py-2" oninput="debouncedApplyCaisseHistoriqueFilters()">
                                <select id="filter-caisse-type" class="border rounded-lg px-3 py-2" onchange="applyCaisseHistoriqueFilters()">
                                    <option value="">Tous les types</option>
                                    <option value="recette">Recettes</option>
                                    <option value="depense">Dépenses</option>
                                    <option value="virement_debit">Virements (débit)</option>
                                    <option value="virement_credit">Virements (crédit)</option>
                                </select>
                                <select id="filter-caisse-account" class="border rounded-lg px-3 py-2" onchange="applyCaisseHistoriqueFilters()">
                                    <option value="">Toutes les caisses</option>
                                    <option value="principale">Caisse Principale</option>
                                    <option value="petite">Petite Caisse</option>
                                    <option value="mobile">Caisse Mobile</option>
                                    <option value="secondaire">Caisse Secondaire</option>
                                </select>
                                <select id="filter-caisse-tiers" class="border rounded-lg px-3 py-2" onchange="applyCaisseHistoriqueFilters()">
                                    <option value="">Tous les tiers</option>
                                </select>
                                <select id="filter-caisse-category" class="border rounded-lg px-3 py-2" onchange="applyCaisseHistoriqueFilters()">
                                    <option value="">Toutes les catégories</option>
                                </select>
                                <select id="filter-caisse-month" class="border rounded-lg px-3 py-2" onchange="applyCaisseHistoriqueFilters()">
                                    <option value="">Tous les mois</option>
                                    <option value="1">Janvier</option>
                                    <option value="2">Février</option>
                                    <option value="3">Mars</option>
                                    <option value="4">Avril</option>
                                    <option value="5">Mai</option>
                                    <option value="6">Juin</option>
                                    <option value="7">Juillet</option>
                                    <option value="8">Août</option>
                                    <option value="9">Septembre</option>
                                    <option value="10">Octobre</option>
                                    <option value="11">Novembre</option>
                                    <option value="12">Décembre</option>
                                </select>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow">
                            <div class="overflow-x-auto">
                                <table class="min-w-full table-auto">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="px-4 py-3 text-left">Date</th>
                                            <th class="px-4 py-3 text-left">Type</th>
                                            <th class="px-4 py-3 text-left">Description</th>
                                            <th class="px-4 py-3 text-left">Caisse</th>
                                            <th class="px-4 py-3 text-left">Tiers</th>
                                            <th class="px-4 py-3 text-left">Catégorie</th>
                                            <th class="px-4 py-3 text-right">Montant</th>
                                            <th class="px-4 py-3 text-right">Solde</th>
                                            <th class="px-4 py-3 text-center no-print">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="caisse-historique-table">
                                        <tr>
                                            <td colspan="9" class="text-center py-4">
                                                <div class="loading mx-auto"></div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>




                    <!-- Contenu Onglet Mouvements -->
                    <div id="caisse-mouvements-content" class="tab-content">
                        <!-- Header avec recherche et filtres améliorés -->
                        <div class="advanced-filters-container">
                            <!-- En-tête principal -->
                            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start mb-6">
                                <div class="mb-4 lg:mb-0 flex-1">
                                    <h4 class="text-2xl font-bold text-gray-800 mb-2">
                                        <i class="fas fa-arrows-alt-v mr-3 text-orange-600"></i>Gestion des Mouvements & Transferts
                                    </h4>
                                    <p class="text-gray-600 text-lg">Effectuez et surveillez tous les mouvements financiers entre vos caisses en temps réel</p>
                                </div>
                            </div>

                            <!-- Système de filtres pour les mouvements -->
                            <div class="filters-header">
                                <h5>
                                    <i class="fas fa-funnel-dollar text-orange-600"></i>
                                    Recherche & Filtres de Mouvements
                                </h5>
                                
                                <!-- Zone de recherche globale pour les mouvements -->
                                <div class="global-search-container">
                                    <input type="text" 
                                           placeholder="Rechercher par description, référence, montant, caisses..." 
                                           class="global-search-input"
                                           id="globalMouvementsSearch"
                                           oninput="performGlobalMouvementsSearch(this.value)">
                                    <i class="fas fa-search global-search-icon"></i>
                                </div>

                                <!-- Icônes de filtres pour les mouvements -->
                                <div class="filter-icons-container">
                                    <div class="relative">
                                        <button class="filter-icon-btn" id="mouvementTypeFilterBtn" onclick="toggleFilterDropdown('mouvementTypeFilter')">
                                            <i class="fas fa-exchange-alt"></i>
                                            <span class="badge" id="mouvementTypeFilterCount" style="display: none;">0</span>
                                        </button>
                                        
                                        <!-- Dropdown Type de mouvement -->
                                        <div class="filter-dropdown" id="mouvementTypeFilterDropdown">
                                            <div class="filter-section">
                                                <h6>Type de Mouvement</h6>
                                                <div class="filter-group">
                                                    <div class="filter-option" onclick="toggleFilterOption(this, 'mouvement_type', 'encaissement')">
                                                        <i class="fas fa-plus mr-1 text-green-500"></i>Encaissement
                                                    </div>
                                                    <div class="filter-option" onclick="toggleFilterOption(this, 'mouvement_type', 'decaissement')">
                                                        <i class="fas fa-minus mr-1 text-red-500"></i>Décaissement
                                                    </div>
                                                    <div class="filter-option" onclick="toggleFilterOption(this, 'mouvement_type', 'virement')">
                                                        <i class="fas fa-exchange-alt mr-1 text-blue-500"></i>Virement
                                                    </div>
                                                    <div class="filter-option" onclick="toggleFilterOption(this, 'mouvement_type', 'transfert')">
                                                        <i class="fas fa-arrow-right mr-1 text-purple-500"></i>Transfert
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="relative">
                                        <button class="filter-icon-btn" id="caissesMouvFilterBtn" onclick="toggleFilterDropdown('caissesMouvFilter')">
                                            <i class="fas fa-cash-register"></i>
                                            <span class="badge" id="caissesMouvFilterCount" style="display: none;">0</span>
                                        </button>
                                        
                                        <!-- Dropdown Caisses -->
                                        <div class="filter-dropdown" id="caissesMouvFilterDropdown">
                                            <div class="filter-section">
                                                <h6>Caisses Impliquées</h6>
                                                <div class="filter-group">
                                                    <div class="filter-option" onclick="toggleFilterOption(this, 'caisse_mouv', 'principale')">
                                                        <span class="w-2 h-2 bg-orange-500 rounded-full inline-block mr-1"></span>Principale
                                                    </div>
                                                    <div class="filter-option" onclick="toggleFilterOption(this, 'caisse_mouv', 'petite')">
                                                        <span class="w-2 h-2 bg-amber-500 rounded-full inline-block mr-1"></span>Petite Caisse
                                                    </div>
                                                    <div class="filter-option" onclick="toggleFilterOption(this, 'caisse_mouv', 'mobile')">
                                                        <span class="w-2 h-2 bg-red-500 rounded-full inline-block mr-1"></span>Mobile
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="relative">
                                        <button class="filter-icon-btn" id="statusMouvFilterBtn" onclick="toggleFilterDropdown('statusMouvFilter')">
                                            <i class="fas fa-check-circle"></i>
                                            <span class="badge" id="statusMouvFilterCount" style="display: none;">0</span>
                                        </button>
                                        
                                        <!-- Dropdown Statut -->
                                        <div class="filter-dropdown" id="statusMouvFilterDropdown">
                                            <div class="filter-section">
                                                <h6>Statut du Mouvement</h6>
                                                <div class="filter-group">
                                                    <div class="filter-option" onclick="toggleFilterOption(this, 'status_mouv', 'valide')">
                                                        <i class="fas fa-check mr-1 text-green-500"></i>Validé
                                                    </div>
                                                    <div class="filter-option" onclick="toggleFilterOption(this, 'status_mouv', 'en_cours')">
                                                        <i class="fas fa-clock mr-1 text-yellow-500"></i>En cours
                                                    </div>
                                                    <div class="filter-option" onclick="toggleFilterOption(this, 'status_mouv', 'rejete')">
                                                        <i class="fas fa-times mr-1 text-red-500"></i>Rejeté
                                                    </div>
                                                    <div class="filter-option" onclick="toggleFilterOption(this, 'status_mouv', 'attente')">
                                                        <i class="fas fa-pause mr-1 text-blue-500"></i>En attente
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Barre des filtres actifs -->
                            <div class="active-filters-bar" id="activeMouvementsFiltersBar" style="display: none;">
                                <span class="text-sm font-medium text-gray-600">Filtres actifs:</span>
                                <div id="activeMouvementsFilterTags"></div>
                                <button class="clear-all-filters" onclick="clearAllMouvementsFilters()">
                                    <i class="fas fa-times mr-1"></i>Tout effacer
                                </button>
                            </div>
                        </div>

                        <!-- Actions rapides améliorées -->
                        <div class="mb-8">
                            <div class="flex items-center justify-between mb-6">
                                <h5 class="text-xl font-bold text-gray-800">
                                    <i class="fas fa-bolt mr-2 text-orange-600"></i>Actions Rapides
                                </h5>
                                <div class="text-sm text-gray-500">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Cliquez pour effectuer une opération express
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <button class="group bg-gradient-to-br from-green-500 to-green-600 text-white p-6 rounded-2xl hover:from-green-600 hover:to-green-700 transition-all duration-300 hover:-translate-y-1 shadow-xl hover:shadow-2xl">
                                    <div class="flex items-center">
                                        <div class="w-16 h-16 bg-white bg-opacity-25 rounded-2xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                                            <i class="fas fa-plus-circle text-3xl"></i>
                                        </div>
                                        <div class="text-left">
                                            <div class="text-xl font-bold mb-1">Encaissement Express</div>
                                            <div class="text-green-200 text-sm">Ajouter des fonds rapidement</div>
                                            <div class="text-green-100 text-xs mt-2">⚡ Action en 2 clics</div>
                                        </div>
                                    </div>
                                </button>
                                <button class="group bg-gradient-to-br from-red-500 to-red-600 text-white p-6 rounded-2xl hover:from-red-600 hover:to-red-700 transition-all duration-300 hover:-translate-y-1 shadow-xl hover:shadow-2xl">
                                    <div class="flex items-center">
                                        <div class="w-16 h-16 bg-white bg-opacity-25 rounded-2xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                                            <i class="fas fa-minus-circle text-3xl"></i>
                                        </div>
                                        <div class="text-left">
                                            <div class="text-xl font-bold mb-1">Décaissement Express</div>
                                            <div class="text-red-200 text-sm">Retirer des fonds rapidement</div>
                                            <div class="text-red-100 text-xs mt-2">⚡ Action en 2 clics</div>
                                        </div>
                                    </div>
                                </button>
                                <button class="group bg-gradient-to-br from-blue-500 to-blue-600 text-white p-6 rounded-2xl hover:from-blue-600 hover:to-blue-700 transition-all duration-300 hover:-translate-y-1 shadow-xl hover:shadow-2xl">
                                    <div class="flex items-center">
                                        <div class="w-16 h-16 bg-white bg-opacity-25 rounded-2xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                                            <i class="fas fa-exchange-alt text-3xl"></i>
                                        </div>
                                        <div class="text-left">
                                            <div class="text-xl font-bold mb-1">Virement Inter-Caisses</div>
                                            <div class="text-blue-200 text-sm">Transférer entre caisses</div>
                                            <div class="text-blue-100 text-xs mt-2">🔄 Transfert sécurisé</div>
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Interface principale améliorée -->
                        <div class="grid grid-cols-1 xl:grid-cols-5 gap-8">
                            <!-- Formulaire de mouvement amélioré -->
                            <div class="xl:col-span-2">
                                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
                                    <div class="flex items-center justify-between mb-6">
                                        <h5 class="text-xl font-bold text-gray-800">
                                            <i class="fas fa-plus-circle mr-3 text-green-600"></i>Nouveau Mouvement
                                        </h5>
                                        <div class="text-sm bg-orange-100 text-orange-600 px-3 py-1 rounded-full font-medium">
                                            <i class="fas fa-shield-alt mr-1"></i>Sécurisé
                                        </div>
                                    </div>
                                    
                                    <div class="space-y-6">
                                        <!-- Caisse source -->
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                                <i class="fas fa-money-check-alt mr-2 text-orange-500"></i>Caisse Source
                                            </label>
                                            <select class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white transition-all duration-200 text-sm font-medium">
                                                <option disabled selected>🎯 Sélectionner la caisse source</option>
                                                <option value="principale">🟠 Caisse Principale • €2,847.30 • Active</option>
                                                <option value="petite">🟡 Petite Caisse • €542.11 • Active</option>
                                                <option value="mobile" disabled>🔴 Caisse Mobile • €0.00 • Fermée</option>
                                            </select>
                                        </div>

                                        <!-- Type de mouvement -->
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                                <i class="fas fa-exchange-alt mr-2 text-blue-500"></i>Type de Mouvement
                                            </label>
                                            <select class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white transition-all duration-200 text-sm font-medium">
                                                <option value="encaissement">📈 Encaissement • Ajouter des fonds</option>
                                                <option value="decaissement">📉 Décaissement • Retirer des fonds</option>
                                                <option value="virement">🔄 Virement vers autre caisse</option>
                                                <option value="banque">🏦 Remise en banque</option>
                                                <option value="correction">🔧 Correction comptable</option>
                                            </select>
                                        </div>

                                        <!-- Caisse destination (conditionnelle) -->
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                                <i class="fas fa-bullseye mr-2 text-purple-500"></i>Caisse Destination
                                                <span class="text-xs text-gray-500 font-normal">(pour les virements)</span>
                                            </label>
                                            <select class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white transition-all duration-200 text-sm font-medium">
                                                <option disabled selected>🎯 Sélectionner la destination</option>
                                                <option value="principale">🟠 Caisse Principale • €2,847.30</option>
                                                <option value="petite">🟡 Petite Caisse • €542.11</option>
                                                <option value="mobile">🔴 Caisse Mobile • €0.00</option>
                                                <option value="banque">🏦 Compte bancaire principal</option>
                                            </select>
                                        </div>

                                        <!-- Montant avec calculateur -->
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                                <i class="fas fa-euro-sign mr-2 text-green-500"></i>Montant
                                            </label>
                                            <div class="relative">
                                                <input type="number" step="0.01" placeholder="0.00" 
                                                       class="w-full px-4 py-3 pr-20 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white transition-all duration-200 text-lg font-bold text-center">
                                                <div class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 font-bold">EUR</div>
                                            </div>
                                            <div class="mt-2 flex gap-2">
                                                <button type="button" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded-lg text-xs font-medium transition-colors">+€50</button>
                                                <button type="button" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded-lg text-xs font-medium transition-colors">+€100</button>
                                                <button type="button" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded-lg text-xs font-medium transition-colors">+€500</button>
                                                <button type="button" class="px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-lg text-xs font-medium transition-colors">Solde complet</button>
                                            </div>
                                        </div>

                                        <!-- Description -->
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                                <i class="fas fa-comment-alt mr-2 text-indigo-500"></i>Description
                                            </label>
                                            <textarea rows="3" placeholder="Décrivez le motif de ce mouvement..." 
                                                      class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white transition-all duration-200 text-sm resize-none"></textarea>
                                        </div>

                                        <!-- Référence -->
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                                <i class="fas fa-hashtag mr-2 text-pink-500"></i>Référence / Justificatif
                                            </label>
                                            <input type="text" placeholder="N° facture, bon de commande, référence..." 
                                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white transition-all duration-200 text-sm font-medium">
                                        </div>

                                        <!-- Actions -->
                                        <div class="flex gap-3 pt-4">
                                            <button class="flex-1 bg-gradient-to-r from-orange-500 to-orange-600 text-white px-6 py-4 rounded-xl font-bold hover:from-orange-600 hover:to-orange-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                                                <i class="fas fa-check mr-2"></i>Valider le Mouvement
                                            </button>
                                            <button class="px-6 py-4 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors font-medium">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <button class="px-6 py-4 bg-blue-100 text-blue-600 rounded-xl hover:bg-blue-200 transition-colors font-medium">
                                                <i class="fas fa-save"></i>
                                            </button>
                                        </div>

                                        <!-- Aide contextuelle -->
                                        <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 mt-4">
                                            <div class="flex items-start space-x-3">
                                                <i class="fas fa-lightbulb text-orange-500 mt-1"></i>
                                                <div>
                                                    <h6 class="text-sm font-bold text-orange-700 mb-1">Aide rapide</h6>
                                                    <p class="text-xs text-orange-600">Les mouvements sont automatiquement validés. Pour les montants > €1000, une confirmation supplémentaire sera demandée.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Dashboard analytique et mouvements -->
                            <div class="xl:col-span-3 space-y-8">
                                <!-- Mouvements récents améliorés -->
                                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                                    <div class="flex justify-between items-center mb-6">
                                        <h5 class="text-xl font-bold text-gray-800">
                                            <i class="fas fa-history mr-3 text-orange-600"></i>Flux de Mouvements en Temps Réel
                                        </h5>
                                        <div class="flex items-center space-x-2">
                                            <div class="text-sm bg-green-100 text-green-600 px-3 py-1 rounded-full font-medium">
                                                <i class="fas fa-circle mr-1 animate-pulse"></i>Live
                                            </div>
                                            <button class="text-sm text-orange-600 hover:text-orange-800 font-medium px-3 py-1 hover:bg-orange-50 rounded-lg transition-colors">Historique complet</button>
                                        </div>
                                    </div>
                                    
                                    <div class="space-y-4">
                                        <!-- Mouvement 1 - Amélioré -->
                                        <div class="group flex items-center justify-between p-5 bg-gradient-to-r from-green-50 to-green-100 rounded-xl border border-green-200 hover:shadow-md transition-all duration-200 hover:-translate-y-1">
                                            <div class="flex items-center space-x-4">
                                                <div class="w-14 h-14 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center text-white shadow-lg group-hover:scale-110 transition-transform">
                                                    <i class="fas fa-arrow-up text-xl"></i>
                                                </div>
                                                <div>
                                                    <p class="font-bold text-gray-800 text-lg">Vente produits - Client ABC Corp</p>
                                                    <div class="flex items-center space-x-3 text-sm text-gray-600 mt-1">
                                                        <span class="flex items-center">
                                                            <i class="fas fa-cash-register mr-1 text-orange-500"></i>Caisse Principale
                                                        </span>
                                                        <span class="flex items-center">
                                                            <i class="fas fa-clock mr-1 text-blue-500"></i>16/10/2025 09:45
                                                        </span>
                                                        <span class="flex items-center">
                                                            <i class="fas fa-user mr-1 text-purple-500"></i>Marie D.
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-2xl font-black text-green-600">+€234.50</p>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    <span class="bg-gray-100 px-2 py-1 rounded-md font-mono">FACT-2025-001</span>
                                                </div>
                                                <div class="text-xs text-green-600 mt-1 font-medium">✓ Validé • CB</div>
                                            </div>
                                        </div>
                                        
                                        <!-- Mouvement 2 - Amélioré -->
                                        <div class="group flex items-center justify-between p-5 bg-gradient-to-r from-red-50 to-red-100 rounded-xl border border-red-200 hover:shadow-md transition-all duration-200 hover:-translate-y-1">
                                            <div class="flex items-center space-x-4">
                                                <div class="w-14 h-14 bg-gradient-to-br from-red-500 to-red-600 rounded-xl flex items-center justify-center text-white shadow-lg group-hover:scale-110 transition-transform">
                                                    <i class="fas fa-arrow-down text-xl"></i>
                                                </div>
                                                <div>
                                                    <p class="font-bold text-gray-800 text-lg">Achat fournitures bureau</p>
                                                    <div class="flex items-center space-x-3 text-sm text-gray-600 mt-1">
                                                        <span class="flex items-center">
                                                            <i class="fas fa-coins mr-1 text-amber-500"></i>Petite Caisse
                                                        </span>
                                                        <span class="flex items-center">
                                                            <i class="fas fa-clock mr-1 text-blue-500"></i>15/10/2025 14:22
                                                        </span>
                                                        <span class="flex items-center">
                                                            <i class="fas fa-user mr-1 text-purple-500"></i>Jean L.
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-2xl font-black text-red-600">-€67.89</p>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    <span class="bg-gray-100 px-2 py-1 rounded-md font-mono">BON-2025-045</span>
                                                </div>
                                                <div class="text-xs text-red-600 mt-1 font-medium">✓ Validé • Espèces</div>
                                            </div>
                                        </div>

                                        <!-- Mouvement 3 - Amélioré -->
                                        <div class="group flex items-center justify-between p-5 bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl border border-blue-200 hover:shadow-md transition-all duration-200 hover:-translate-y-1">
                                            <div class="flex items-center space-x-4">
                                                <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg group-hover:scale-110 transition-transform">
                                                    <i class="fas fa-exchange-alt text-xl"></i>
                                                </div>
                                                <div>
                                                    <p class="font-bold text-gray-800 text-lg">Remise en banque journalière</p>
                                                    <div class="flex items-center space-x-3 text-sm text-gray-600 mt-1">
                                                        <span class="flex items-center">
                                                            <i class="fas fa-university mr-1 text-blue-500"></i>Vers Banque
                                                        </span>
                                                        <span class="flex items-center">
                                                            <i class="fas fa-clock mr-1 text-blue-500"></i>14/10/2025 16:30
                                                        </span>
                                                        <span class="flex items-center">
                                                            <i class="fas fa-robot mr-1 text-gray-500"></i>Auto
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-2xl font-black text-blue-600">-€1,000.00</p>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    <span class="bg-gray-100 px-2 py-1 rounded-md font-mono">VIR-2025-012</span>
                                                </div>
                                                <div class="text-xs text-blue-600 mt-1 font-medium">✓ Traité • Virement</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tableau de bord analytique du jour -->
                                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                                    <div class="flex items-center justify-between mb-6">
                                        <h5 class="text-xl font-bold text-gray-800">
                                            <i class="fas fa-chart-pie mr-3 text-orange-600"></i>Analytics & Performance du Jour
                                        </h5>
                                        <div class="text-sm text-gray-500">
                                            <i class="fas fa-calendar-day mr-1"></i>16 Octobre 2025
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                        <!-- Encaissements -->
                                        <div class="text-center p-6 bg-gradient-to-br from-green-50 to-green-100 rounded-2xl border border-green-200">
                                            <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl mx-auto mb-4 flex items-center justify-center shadow-lg">
                                                <i class="fas fa-plus text-white text-2xl"></i>
                                            </div>
                                            <div class="text-3xl font-black text-green-600 mb-1">€847.30</div>
                                            <div class="text-sm font-bold text-green-700 mb-2">Encaissements</div>
                                            <div class="text-xs text-gray-600">
                                                <span class="bg-green-200 text-green-800 px-2 py-1 rounded-full font-medium">8 opérations</span>
                                            </div>
                                            <div class="text-xs text-green-600 mt-2">↗ +12% vs hier</div>
                                        </div>

                                        <!-- Décaissements -->
                                        <div class="text-center p-6 bg-gradient-to-br from-red-50 to-red-100 rounded-2xl border border-red-200">
                                            <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-red-600 rounded-2xl mx-auto mb-4 flex items-center justify-center shadow-lg">
                                                <i class="fas fa-minus text-white text-2xl"></i>
                                            </div>
                                            <div class="text-3xl font-black text-red-600 mb-1">€125.60</div>
                                            <div class="text-sm font-bold text-red-700 mb-2">Décaissements</div>
                                            <div class="text-xs text-gray-600">
                                                <span class="bg-red-200 text-red-800 px-2 py-1 rounded-full font-medium">3 opérations</span>
                                            </div>
                                            <div class="text-xs text-red-600 mt-2">↘ -5% vs hier</div>
                                        </div>

                                        <!-- Solde Net -->
                                        <div class="text-center p-6 bg-gradient-to-br from-orange-50 to-orange-100 rounded-2xl border border-orange-200">
                                            <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl mx-auto mb-4 flex items-center justify-center shadow-lg">
                                                <i class="fas fa-calculator text-white text-2xl"></i>
                                            </div>
                                            <div class="text-3xl font-black text-orange-600 mb-1">€721.70</div>
                                            <div class="text-sm font-bold text-orange-700 mb-2">Solde Net</div>
                                            <div class="text-xs text-gray-600">
                                                <span class="bg-orange-200 text-orange-800 px-2 py-1 rounded-full font-medium">Variation</span>
                                            </div>
                                            <div class="text-xs text-orange-600 mt-2">↗ Positif</div>
                                        </div>

                                        <!-- Virements -->
                                        <div class="text-center p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl border border-blue-200">
                                            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl mx-auto mb-4 flex items-center justify-center shadow-lg">
                                                <i class="fas fa-exchange-alt text-white text-2xl"></i>
                                            </div>
                                            <div class="text-3xl font-black text-blue-600 mb-1">€1,000</div>
                                            <div class="text-sm font-bold text-blue-700 mb-2">Virements</div>
                                            <div class="text-xs text-gray-600">
                                                <span class="bg-blue-200 text-blue-800 px-2 py-1 rounded-full font-medium">1 opération</span>
                                            </div>
                                            <div class="text-xs text-blue-600 mt-2">🏦 Vers banque</div>
                                        </div>
                                    </div>

                                    <!-- Graphiques et indicateurs supplémentaires -->
                                    <div class="mt-6 pt-6 border-t border-gray-200">
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div class="text-center p-4 bg-gray-50 rounded-xl">
                                                <div class="text-lg font-bold text-gray-800">15.2%</div>
                                                <div class="text-sm text-gray-600">Taux de rotation</div>
                                            </div>
                                            <div class="text-center p-4 bg-gray-50 rounded-xl">
                                                <div class="text-lg font-bold text-gray-800">€109.64</div>
                                                <div class="text-sm text-gray-600">Montant moyen</div>
                                            </div>
                                            <div class="text-center p-4 bg-gray-50 rounded-xl">
                                                <div class="text-lg font-bold text-gray-800">2h 15m</div>
                                                <div class="text-sm text-gray-600">Temps entre ops</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contenu Onglet Rapports -->
                    <div id="caisse-rapports-content" class="tab-content">
                        <!-- Header -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                            <div class="mb-6">
                                <h4 class="text-xl font-bold text-gray-800 mb-2">
                                    <i class="fas fa-chart-bar mr-2 text-orange-600"></i>Rapports et Analyses de Caisse
                                </h4>
                                <p class="text-gray-600">Générez des rapports détaillés et analysez les performances de vos caisses</p>
                            </div>

                            <!-- Types de rapports rapides -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <button class="p-4 bg-blue-50 rounded-lg border border-blue-200 hover:bg-blue-100 transition-colors">
                                    <i class="fas fa-file-alt text-2xl text-blue-600 mb-2"></i>
                                    <div class="text-sm font-semibold text-blue-800">Situation Journalière</div>
                                </button>
                                <button class="p-4 bg-green-50 rounded-lg border border-green-200 hover:bg-green-100 transition-colors">
                                    <i class="fas fa-calendar-week text-2xl text-green-600 mb-2"></i>
                                    <div class="text-sm font-semibold text-green-800">Rapport Hebdomadaire</div>
                                </button>
                                <button class="p-4 bg-purple-50 rounded-lg border border-purple-200 hover:bg-purple-100 transition-colors">
                                    <i class="fas fa-calendar-alt text-2xl text-purple-600 mb-2"></i>
                                    <div class="text-sm font-semibold text-purple-800">Rapport Mensuel</div>
                                </button>
                                <button class="p-4 bg-orange-50 rounded-lg border border-orange-200 hover:bg-orange-100 transition-colors">
                                    <i class="fas fa-cog text-2xl text-orange-600 mb-2"></i>
                                    <div class="text-sm font-semibold text-orange-800">Rapport Personnalisé</div>
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                            <!-- Configuration du rapport -->
                            <div class="xl:col-span-1">
                                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                                    <h5 class="text-lg font-bold text-gray-800 mb-4">
                                        <i class="fas fa-cogs mr-2 text-orange-600"></i>Configuration du Rapport
                                    </h5>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Type de rapport</label>
                                            <select class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white">
                                                <option>📊 Situation de caisse</option>
                                                <option>📋 Journal de caisse</option>
                                                <option>🔄 Réconciliation bancaire</option>
                                                <option>📈 Analyse des mouvements</option>
                                                <option>💰 Bilan financier</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Période</label>
                                            <select class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white">
                                                <option>📅 Aujourd'hui</option>
                                                <option>📊 Cette semaine</option>
                                                <option>🗓️ Ce mois</option>
                                                <option>🎯 Période personnalisée</option>
                                            </select>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Date début</label>
                                                <input type="date" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Date fin</label>
                                                <input type="date" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Caisses à inclure</label>
                                            <div class="space-y-2">
                                                <label class="flex items-center">
                                                    <input type="checkbox" checked class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                                    <span class="ml-2 text-sm">🟠 Caisse Principale</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="checkbox" checked class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                                    <span class="ml-2 text-sm">🟡 Petite Caisse</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="checkbox" class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                                    <span class="ml-2 text-sm">🔴 Caisse Mobile</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="space-y-3">
                                            <button class="w-full btn-modern btn-accent-modern px-4 py-2.5">
                                                <i class="fas fa-chart-bar mr-2"></i>Générer le Rapport
                                            </button>
                                            <div class="grid grid-cols-2 gap-2">
                                                <button class="btn-modern bg-red-500 text-white px-4 py-2 hover:bg-red-600">
                                                    <i class="fas fa-file-pdf mr-1"></i>PDF
                                                </button>
                                                <button class="btn-modern bg-green-500 text-white px-4 py-2 hover:bg-green-600">
                                                    <i class="fas fa-file-excel mr-1"></i>Excel
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Statistiques et visualisations -->
                            <div class="xl:col-span-2 space-y-6">
                                <!-- Statistiques principales -->
                                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                                    <h5 class="text-lg font-bold text-gray-800 mb-4">
                                        <i class="fas fa-chart-pie mr-2 text-orange-600"></i>Statistiques Principales
                                    </h5>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="p-4 bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg border border-orange-200">
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <p class="text-sm font-medium text-orange-700">Solde Total Caisses</p>
                                                    <p class="text-2xl font-bold text-orange-600">€3,389.41</p>
                                                </div>
                                                <div class="w-12 h-12 bg-orange-200 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-coins text-orange-600 text-xl"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-lg border border-green-200">
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <p class="text-sm font-medium text-green-700">Encaissements du mois</p>
                                                    <p class="text-2xl font-bold text-green-600">€12,459.80</p>
                                                </div>
                                                <div class="w-12 h-12 bg-green-200 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-arrow-up text-green-600 text-xl"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="p-4 bg-gradient-to-br from-red-50 to-red-100 rounded-lg border border-red-200">
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <p class="text-sm font-medium text-red-700">Décaissements du mois</p>
                                                    <p class="text-2xl font-bold text-red-600">€9,070.39</p>
                                                </div>
                                                <div class="w-12 h-12 bg-red-200 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-arrow-down text-red-600 text-xl"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg border border-blue-200">
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <p class="text-sm font-medium text-blue-700">Nombre d'opérations</p>
                                                    <p class="text-2xl font-bold text-blue-600">342</p>
                                                </div>
                                                <div class="w-12 h-12 bg-blue-200 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-list text-blue-600 text-xl"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Répartition par caisse -->
                                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                                    <h5 class="text-lg font-bold text-gray-800 mb-4">
                                        <i class="fas fa-chart-donut mr-2 text-orange-600"></i>Répartition par Caisse
                                    </h5>
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
                                            <div class="flex items-center">
                                                <div class="w-4 h-4 bg-orange-500 rounded-full mr-3"></div>
                                                <span class="font-medium text-gray-800">Caisse Principale</span>
                                            </div>
                                            <div class="text-right">
                                                <span class="font-bold text-orange-600">€2,847.30</span>
                                                <div class="text-xs text-gray-500">84.0%</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between p-3 bg-amber-50 rounded-lg">
                                            <div class="flex items-center">
                                                <div class="w-4 h-4 bg-amber-500 rounded-full mr-3"></div>
                                                <span class="font-medium text-gray-800">Petite Caisse</span>
                                            </div>
                                            <div class="text-right">
                                                <span class="font-bold text-amber-600">€542.11</span>
                                                <div class="text-xs text-gray-500">16.0%</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <div class="flex items-center">
                                                <div class="w-4 h-4 bg-gray-400 rounded-full mr-3"></div>
                                                <span class="font-medium text-gray-800">Caisse Mobile</span>
                                            </div>
                                            <div class="text-right">
                                                <span class="font-bold text-gray-500">€0.00</span>
                                                <div class="text-xs text-gray-500">0.0%</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Achats (Dépenses) avec Onglets -->
                <div id="achats-section" class="section" style="display: none;">
                    <!-- Header de la section -->
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-2xl font-bold text-gray-800">
                            <i class="fas fa-shopping-cart mr-3 text-red-600"></i>Gestion des Achats et Dépenses
                        </h3>
                        <div class="flex gap-3">
                            <button onclick="openAchatModal()" class="btn-modern btn-primary-modern px-6 py-3 no-print">
                                <i class="fas fa-plus mr-2"></i>Nouvel Achat
                            </button>
                            <button onclick="openSupplierModal()" class="btn-modern btn-accent-modern px-6 py-3 no-print">
                                <i class="fas fa-building mr-2"></i>Gérer Fournisseurs
                            </button>
                        </div>
                    </div>

                    <!-- Système d'onglets Achats -->
                    <div class="flex justify-between items-center mb-6">
                        <div class="section-tabs">
                            <button class="section-tab achats-tab active" onclick="showAchatsTab('vue-ensemble')">
                                <i class="fas fa-tachometer-alt mr-2"></i>Vue d'ensemble
                            </button>
                            <button class="section-tab achats-tab" onclick="showAchatsTab('enregistrements')">
                                <i class="fas fa-file-invoice mr-2"></i>Enregistrements
                            </button>
                            <button class="section-tab achats-tab" onclick="showAchatsTab('suivi-paiements')">
                                <i class="fas fa-credit-card mr-2"></i>Suivi des Paiements
                            </button>
                            <button class="section-tab achats-tab" onclick="showAchatsTab('categories')">
                                <i class="fas fa-tags mr-2"></i>Catégories
                            </button>
                            <button class="section-tab achats-tab" onclick="showAchatsTab('rapports')">
                                <i class="fas fa-chart-bar mr-2"></i>Rapports
                            </button>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="relative">
                                <input type="text" placeholder="Rechercher achats..." 
                                       class="pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            </div>
                            <button class="btn-modern bg-gradient-to-r from-blue-500 to-blue-600 text-white px-4 py-2 hover:from-blue-600 hover:to-blue-700">
                                <i class="fas fa-download mr-2"></i>Exporter
                            </button>
                        </div>
                    </div>

                    <!-- Contenu Onglet Vue d'ensemble -->
                    <div id="achats-vue-ensemble-content" class="tab-content active">
                        <!-- KPIs et métriques -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                            <!-- Total Dépenses Mois -->
                            <div class="bg-gradient-to-br from-red-500 to-red-600 text-white rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="bg-white bg-opacity-25 p-3 rounded-xl">
                                        <i class="fas fa-shopping-bag text-2xl"></i>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-3xl font-bold">€8,547.30</div>
                                        <div class="text-red-100 text-sm">Ce mois</div>
                                    </div>
                                </div>
                                <div class="border-t border-red-400 pt-3">
                                    <p class="text-red-100 text-sm font-medium">Total des dépenses</p>
                                    <div class="flex items-center justify-between text-xs text-red-200 mt-1">
                                        <span>vs mois dernier</span>
                                        <span class="font-semibold">+12.5%</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Fournisseurs Actifs -->
                            <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="bg-white bg-opacity-25 p-3 rounded-xl">
                                        <i class="fas fa-building text-2xl"></i>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-3xl font-bold">24</div>
                                        <div class="text-blue-100 text-sm">Fournisseurs</div>
                                    </div>
                                </div>
                                <div class="border-t border-blue-400 pt-3">
                                    <p class="text-blue-100 text-sm font-medium">Fournisseurs actifs</p>
                                    <div class="flex items-center justify-between text-xs text-blue-200 mt-1">
                                        <span>nouveaux ce mois</span>
                                        <span class="font-semibold">+3</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Factures en Attente -->
                            <div class="bg-gradient-to-br from-amber-500 to-amber-600 text-white rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="bg-white bg-opacity-25 p-3 rounded-xl">
                                        <i class="fas fa-clock text-2xl"></i>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-3xl font-bold">7</div>
                                        <div class="text-amber-100 text-sm">En attente</div>
                                    </div>
                                </div>
                                <div class="border-t border-amber-400 pt-3">
                                    <p class="text-amber-100 text-sm font-medium">Factures à payer</p>
                                    <div class="flex items-center justify-between text-xs text-amber-200 mt-1">
                                        <span>montant total</span>
                                        <span class="font-semibold">€2,341.80</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Dépenses par Catégorie -->
                            <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="bg-white bg-opacity-25 p-3 rounded-xl">
                                        <i class="fas fa-chart-pie text-2xl"></i>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-3xl font-bold">6</div>
                                        <div class="text-green-100 text-sm">Catégories</div>
                                    </div>
                                </div>
                                <div class="border-t border-green-400 pt-3">
                                    <p class="text-green-100 text-sm font-medium">Types de dépenses</p>
                                    <div class="flex items-center justify-between text-xs text-green-200 mt-1">
                                        <span>plus grosse</span>
                                        <span class="font-semibold">Fournitures</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Graphiques et Analyses -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                            <!-- Évolution Mensuelle -->
                            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                                <h5 class="text-xl font-bold text-gray-800 mb-6">
                                    <i class="fas fa-chart-line mr-3 text-red-600"></i>Évolution Mensuelle des Dépenses
                                </h5>
                                <div class="h-64">
                                    <canvas id="depensesEvolutionChart"></canvas>
                                </div>
                                <div class="mt-4 grid grid-cols-3 gap-4 text-center">
                                    <div class="p-3 bg-red-50 rounded-lg">
                                        <div class="text-lg font-bold text-red-600">€8,547</div>
                                        <div class="text-sm text-gray-600">Oct 2025</div>
                                    </div>
                                    <div class="p-3 bg-gray-50 rounded-lg">
                                        <div class="text-lg font-bold text-gray-600">€7,598</div>
                                        <div class="text-sm text-gray-600">Sep 2025</div>
                                    </div>
                                    <div class="p-3 bg-gray-50 rounded-lg">
                                        <div class="text-lg font-bold text-gray-600">€6,234</div>
                                        <div class="text-sm text-gray-600">Aoû 2025</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Répartition par Catégorie -->
                            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                                <h5 class="text-xl font-bold text-gray-800 mb-6">
                                    <i class="fas fa-chart-donut mr-3 text-red-600"></i>Répartition par Catégorie
                                </h5>
                                <div class="h-64">
                                    <canvas id="repartitionCategoriesChart"></canvas>
                                </div>
                                <div class="mt-4 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 bg-red-500 rounded-full mr-3"></div>
                                            <span class="text-sm font-medium">Fournitures</span>
                                        </div>
                                        <span class="text-sm font-bold">€3,419 (40%)</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                                            <span class="text-sm font-medium">Énergie</span>
                                        </div>
                                        <span class="text-sm font-bold">€2,564 (30%)</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                                            <span class="text-sm font-medium">Salaires</span>
                                        </div>
                                        <span class="text-sm font-bold">€1,709 (20%)</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 bg-yellow-500 rounded-full mr-3"></div>
                                            <span class="text-sm font-medium">Autres</span>
                                        </div>
                                        <span class="text-sm font-bold">€855 (10%)</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dernières Dépenses -->
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                            <div class="flex justify-between items-center mb-6">
                                <h5 class="text-xl font-bold text-gray-800">
                                    <i class="fas fa-history mr-3 text-red-600"></i>Dernières Dépenses
                                </h5>
                                <button onclick="showAchatsTab('enregistrements')" class="text-red-600 hover:text-red-800 font-medium">
                                    Voir tout <i class="fas fa-arrow-right ml-1"></i>
                                </button>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead>
                                        <tr class="border-b border-gray-200">
                                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Date</th>
                                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Fournisseur</th>
                                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Description</th>
                                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Catégorie</th>
                                            <th class="text-right py-3 px-4 font-semibold text-gray-700">Montant TTC</th>
                                            <th class="text-center py-3 px-4 font-semibold text-gray-700">Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-3 px-4 text-gray-600">25/10/2025</td>
                                            <td class="py-3 px-4 font-medium">Fournitures Pro SARL</td>
                                            <td class="py-3 px-4">Achat matériel bureau</td>
                                            <td class="py-3 px-4">
                                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Fournitures</span>
                                            </td>
                                            <td class="py-3 px-4 text-right font-semibold">€456.78</td>
                                            <td class="py-3 px-4 text-center">
                                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Payé</span>
                                            </td>
                                        </tr>
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-3 px-4 text-gray-600">24/10/2025</td>
                                            <td class="py-3 px-4 font-medium">EDF Business</td>
                                            <td class="py-3 px-4">Facture électricité - Septembre</td>
                                            <td class="py-3 px-4">
                                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">Énergie</span>
                                            </td>
                                            <td class="py-3 px-4 text-right font-semibold">€234.56</td>
                                            <td class="py-3 px-4 text-center">
                                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">En attente</span>
                                            </td>
                                        </tr>
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-3 px-4 text-gray-600">23/10/2025</td>
                                            <td class="py-3 px-4 font-medium">Nettoyage Expert</td>
                                            <td class="py-3 px-4">Service nettoyage mensuel</td>
                                            <td class="py-3 px-4">
                                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Services</span>
                                            </td>
                                            <td class="py-3 px-4 text-right font-semibold">€189.90</td>
                                            <td class="py-3 px-4 text-center">
                                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Payé</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Contenu Onglet Enregistrements -->
                    <div id="achats-enregistrements-content" class="tab-content">
                        <div class="flex flex-wrap justify-between items-center mb-6 gap-2">
                            <h3 class="text-xl font-semibold">Enregistrement des Achats et Dépenses</h3>
                            <div class="flex gap-2">
                                <button onclick="openPurchaseModal()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg no-print">
                                    <i class="fas fa-plus mr-2"></i>Nouvel Achat
                                </button>
                                <button onclick="importPurchasesModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg no-print">
                                    <i class="fas fa-upload mr-2"></i>Importer
                                </button>
                            </div>
                        </div>

                        <!-- Filtres avancés -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                            <h4 class="text-lg font-semibold mb-4">Filtres de Recherche</h4>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Période</label>
                                    <select class="w-full border rounded-lg px-3 py-2" onchange="applyPurchaseFilters()">
                                        <option value="">Toutes les périodes</option>
                                        <option value="today">Aujourd'hui</option>
                                        <option value="week">Cette semaine</option>
                                        <option value="month">Ce mois</option>
                                        <option value="quarter">Ce trimestre</option>
                                        <option value="year">Cette année</option>
                                        <option value="custom">Période personnalisée</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Fournisseur</label>
                                    <select class="w-full border rounded-lg px-3 py-2" onchange="applyPurchaseFilters()">
                                        <option value="">Tous les fournisseurs</option>
                                        <option value="fournitures">Fournitures Pro SARL</option>
                                        <option value="energie">EDF Business</option>
                                        <option value="nettoyage">Nettoyage Expert</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                                    <select class="w-full border rounded-lg px-3 py-2" onchange="applyPurchaseFilters()">
                                        <option value="">Toutes les catégories</option>
                                        <option value="fournitures">Fournitures</option>
                                        <option value="energie">Énergie</option>
                                        <option value="salaires">Salaires</option>
                                        <option value="services">Services</option>
                                        <option value="autres">Autres</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                                    <select class="w-full border rounded-lg px-3 py-2" onchange="applyPurchaseFilters()">
                                        <option value="">Tous les statuts</option>
                                        <option value="paye">Payé</option>
                                        <option value="en_attente">En attente</option>
                                        <option value="partiel">Partiellement payé</option>
                                        <option value="annule">Annulé</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Tableau des achats -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full table-auto">
                                    <thead>
                                        <tr class="bg-gray-50 border-b">
                                            <th class="px-4 py-3 text-left">
                                                <input type="checkbox" class="rounded border-gray-300">
                                            </th>
                                            <th class="px-4 py-3 text-left">Date</th>
                                            <th class="px-4 py-3 text-left">Fournisseur</th>
                                            <th class="px-4 py-3 text-left">Description</th>
                                            <th class="px-4 py-3 text-left">Catégorie</th>
                                            <th class="px-4 py-3 text-left">Mode Paiement</th>
                                            <th class="px-4 py-3 text-left">Compte</th>
                                            <th class="px-4 py-3 text-right">Montant TTC</th>
                                            <th class="px-4 py-3 text-center">Statut</th>
                                            <th class="px-4 py-3 text-center no-print">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="achats-table">
                                        <tr>
                                            <td colspan="10" class="text-center py-8">
                                                <div class="loading mx-auto"></div>
                                                <p class="text-gray-500 mt-3">Chargement des achats...</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Contenu Onglet Suivi des Paiements -->
                    <div id="achats-suivi-paiements-content" class="tab-content">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold">Suivi des Paiements</h3>
                            <div class="flex gap-3">
                                <button onclick="generatePaymentSchedule()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                                    <i class="fas fa-calendar mr-2"></i>Échéancier
                                </button>
                                <button onclick="exportPayments()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">
                                    <i class="fas fa-download mr-2"></i>Exporter
                                </button>
                            </div>
                        </div>

                        <!-- Statistiques de paiements -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                                <div class="flex items-center">
                                    <div class="p-3 bg-green-100 rounded-lg">
                                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-600">Payé</p>
                                        <p class="text-2xl font-semibold text-gray-900">€6,205.50</p>
                                        <p class="text-sm text-gray-500">72.6% du total</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                                <div class="flex items-center">
                                    <div class="p-3 bg-yellow-100 rounded-lg">
                                        <i class="fas fa-clock text-yellow-600 text-xl"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-600">En attente</p>
                                        <p class="text-2xl font-semibold text-gray-900">€2,341.80</p>
                                        <p class="text-sm text-gray-500">27.4% du total</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                                <div class="flex items-center">
                                    <div class="p-3 bg-red-100 rounded-lg">
                                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-600">En retard</p>
                                        <p class="text-2xl font-semibold text-gray-900">€856.20</p>
                                        <p class="text-sm text-gray-500">3 factures</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tableau de suivi -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                            <div class="p-6 border-b border-gray-200">
                                <h4 class="text-lg font-semibold">État des Paiements</h4>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fournisseur</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Facture</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date Échéance</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Montant</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Statut</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Fournitures Pro SARL</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">FACT-2025-089</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">15/11/2025</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-right font-medium">€456.78</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Payé</span>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-medium">
                                                <button onclick="viewPaymentDetails('123')" class="text-blue-600 hover:text-blue-900 mr-3">Voir</button>
                                                <button onclick="downloadInvoice('123')" class="text-green-600 hover:text-green-900">Télécharger</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">EDF Business</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">FACT-2025-087</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-red-500">10/10/2025</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-right font-medium">€234.56</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">En retard</span>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-medium">
                                                <button onclick="viewPaymentDetails('124')" class="text-blue-600 hover:text-blue-900 mr-3">Voir</button>
                                                <button onclick="markAsPaid('124')" class="text-green-600 hover:text-green-900">Marquer payé</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Contenu Onglet Catégories -->
                    <div id="achats-categories-content" class="tab-content">
                        <!-- Header avec titre et bouton Nouvelle Catégorie -->
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold">Gestion des Catégories de Dépenses</h3>
                            <div class="flex space-x-3">
                                <button onclick="loadAchatsCategories()" class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors" title="Actualiser">
                                    <i class="fas fa-sync-alt mr-2"></i>Actualiser
                                </button>
                                <button onclick="openCategoryModal()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-plus mr-2"></i>Nouvelle Catégorie
                                </button>
                            </div>
                        </div>

                        <!-- Liste des catégories dynamiques -->
                        <div id="categories-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                            <div class="col-span-full text-center py-8 text-gray-500">
                                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                <p>Chargement des catégories...</p>
                            </div>
                        </div>

                        <!-- Message si aucune catégorie -->
                        <div id="no-categories-message" class="hidden text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                            <i class="fas fa-tag text-4xl text-gray-400 mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune catégorie</h3>
                            <p class="text-gray-500 mb-4">Aucune catégorie n'a été configurée</p>
                            <button onclick="openCategoryModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-plus mr-2"></i>Créer votre première catégorie
                            </button>
                        </div>


                    </div>

                    <!-- Contenu Onglet Rapports -->
                    <div id="achats-rapports-content" class="tab-content">
                        <div class="mb-6">
                            <h3 class="text-xl font-semibold mb-2">Rapports et Analyses des Achats</h3>
                            <p class="text-gray-600">Analysez vos dépenses et optimisez vos budgets</p>
                        </div>

                        <!-- Options de rapport -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                            <h4 class="text-lg font-semibold mb-4">Paramètres du Rapport</h4>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Période</label>
                                    <select class="w-full border rounded-lg px-3 py-2">
                                        <option value="month">Ce mois</option>
                                        <option value="quarter">Ce trimestre</option>
                                        <option value="year">Cette année</option>
                                        <option value="custom">Période personnalisée</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Catégories à inclure</label>
                                    <select class="w-full border rounded-lg px-3 py-2">
                                        <option value="all">Toutes les catégories</option>
                                        <option value="main">Catégories principales</option>
                                        <option value="custom">Sélection personnalisée</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Fournisseurs</label>
                                    <select class="w-full border rounded-lg px-3 py-2">
                                        <option value="all">Tous les fournisseurs</option>
                                        <option value="top5">Top 5 fournisseurs</option>
                                        <option value="active">Fournisseurs actifs</option>
                                    </select>
                                </div>
                                <div class="flex items-end">
                                    <button class="w-full bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">
                                        <i class="fas fa-chart-bar mr-2"></i>Générer
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Graphiques de rapport -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                            <!-- Évolution par mois -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h5 class="text-lg font-semibold mb-4">Évolution Mensuelle</h5>
                                <div class="h-64">
                                    <canvas id="evolutionMensuelleChart"></canvas>
                                </div>
                            </div>

                            <!-- Top fournisseurs -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h5 class="text-lg font-semibold mb-4">Top 5 Fournisseurs</h5>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 bg-red-500 rounded-full mr-3"></div>
                                            <span class="font-medium">Fournitures Pro SARL</span>
                                        </div>
                                        <span class="font-bold">€4,234.50</span>
                                    </div>
                                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                                            <span class="font-medium">EDF Business</span>
                                        </div>
                                        <span class="font-bold">€2,145.30</span>
                                    </div>
                                    <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                                            <span class="font-medium">Nettoyage Expert</span>
                                        </div>
                                        <span class="font-bold">€1,234.00</span>
                                    </div>
                                    <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 bg-yellow-500 rounded-full mr-3"></div>
                                            <span class="font-medium">Assurances Plus</span>
                                        </div>
                                        <span class="font-bold">€567.80</span>
                                    </div>
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 bg-gray-500 rounded-full mr-3"></div>
                                            <span class="font-medium">Autres</span>
                                        </div>
                                        <span class="font-bold">€365.70</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Détail par catégorie -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h5 class="text-lg font-semibold">Détail par Catégorie</h5>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catégorie</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Montant</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">% du Total</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Nb Factures</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Moyenne</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="w-3 h-3 bg-red-500 rounded-full mr-3"></div>
                                                    <span class="font-medium text-gray-900">Fournitures</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-right font-semibold">€3,419.12</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-right">40.0%</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-center">12</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-right">€284.93</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                                                    <span class="font-medium text-gray-900">Énergie</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-right font-semibold">€2,564.30</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-right">30.0%</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-center">3</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-right">€854.77</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                                                    <span class="font-medium text-gray-900">Salaires</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-right font-semibold">€1,709.88</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-right">20.0%</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-center">1</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-right">€1,709.88</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="w-3 h-3 bg-yellow-500 rounded-full mr-3"></div>
                                                    <span class="font-medium text-gray-900">Services</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-right font-semibold">€854.00</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-right">10.0%</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-center">4</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-right">€213.50</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rapports Section -->
                <div id="rapports-section" class="section" style="display: none;">
                    <h3 class="text-xl font-semibold mb-6">Rapports</h3>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow">
                            <h4 class="text-lg font-semibold mb-4">Générer un Rapport</h4>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Type de rapport</label>
                                    <select id="rapport-type" class="mt-1 block w-full border rounded-lg px-3 py-2" onchange="togglePeriodeFields()">
                                        <option value="mensuel">Rapport Mensuel</option>
                                        <option value="annuel">Rapport Annuel</option>
                                        <option value="periode">Période Personnalisée</option>
                                    </select>
                                </div>
                                <div id="periode-fields" style="display: none;">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Date de début</label>
                                            <input type="date" id="rapport-debut" class="mt-1 block w-full border rounded-lg px-3 py-2">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Date de fin</label>
                                            <input type="date" id="rapport-fin" class="mt-1 block w-full border rounded-lg px-3 py-2">
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <button onclick="generateReport()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                                        <i class="fas fa-file-alt mr-2"></i>Générer
                                    </button>
                                    <button onclick="exportToPDF()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">
                                        <i class="fas fa-file-pdf mr-2"></i>Export PDF
                                    </button>
                                </div>
                                <button onclick="exportToExcel()" class="w-full bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">
                                    <i class="fas fa-file-excel mr-2"></i>Export Excel
                                </button>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-lg shadow">
                            <h4 class="text-lg font-semibold mb-4">Résumé Actuel</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span>Total Recettes:</span>
                                    <span id="rapport-recettes" class="font-semibold text-green-600">0,00 €</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Total Dépenses:</span>
                                    <span id="rapport-depenses" class="font-semibold text-red-600">0,00 €</span>
                                </div>
                                <div class="flex justify-between border-t pt-3">
                                    <span class="font-semibold">Solde Final:</span>
                                    <span id="rapport-solde" class="font-bold">0,00 €</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Nombre de transactions:</span>
                                    <span id="rapport-nb-transactions">0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="rapport-details" class="bg-white rounded-lg shadow" style="display: none;">
                        <div class="p-6">
                            <h4 class="text-lg font-semibold mb-4">Détails du Rapport</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full table-auto">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="px-4 py-3 text-left">Date</th>
                                            <th class="px-4 py-3 text-left">Type</th>
                                            <th class="px-4 py-3 text-left">Description</th>
                                            <th class="px-4 py-3 text-left">Compte</th>
                                            <th class="px-4 py-3 text-right">Montant</th>
                                        </tr>
                                    </thead>
                                    <tbody id="rapport-table">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Parametres Section -->
                <div id="parametres-section" class="section" style="display: none;">
                    <h3 class="text-xl font-semibold mb-6">Paramètres</h3>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-white p-6 rounded-lg shadow">
                            <h4 class="text-lg font-semibold mb-4">Catégories</h4>
                            <div class="mb-4">
                                <div class="flex gap-2 mb-4">
                                    <input type="text" id="new-category" placeholder="Nouvelle catégorie" class="flex-1 border rounded-lg px-3 py-2">
                                    <button onclick="addCategory()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <div id="categories-list" class="space-y-2">
                                    <!-- Categories will be loaded here -->
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-lg shadow">
                            <h4 class="text-lg font-semibold mb-4">Configuration</h4>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Devise</label>
                                    <select id="currency-setting" class="mt-1 block w-full border rounded-lg px-3 py-2">
                                        <option value="EUR">Euro (€)</option>
                                        <option value="USD">Dollar ($)</option>
                                        <option value="GBP">Livre (£)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nom de l'organisation</label>
                                    <input type="text" id="org-name" placeholder="Nom de l'organisation" class="mt-1 block w-full border rounded-lg px-3 py-2">
                                </div>
                                <button onclick="saveSettings()" class="w-full bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">
                                    <i class="fas fa-save mr-2"></i>Sauvegarder
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow mt-6">
                        <h4 class="text-lg font-semibold mb-4">Sauvegarde et Restauration</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <button onclick="exportData()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                                <i class="fas fa-download mr-2"></i>Exporter les données
                            </button>
                            <label class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg cursor-pointer text-center">
                                <i class="fas fa-upload mr-2"></i>Importer les données
                                <input type="file" id="import-data" accept=".json" style="display: none;" onchange="importData(event)">
                            </label>
                            <button onclick="clearAllData()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">
                                <i class="fas fa-trash mr-2"></i>Effacer toutes les données
                            </button>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modals (identiques à l'original) -->
    <!-- Transaction Modal -->
    <div id="transactionModal" class="modal">
        <div class="modal-content" style="max-width: 1000px; max-height: 90vh; overflow-y: auto;">
            <div class="modal-header">
                <h3 class="text-lg font-semibold"><i class="fas fa-exchange-alt mr-2 text-blue-600"></i>Nouvelle Transaction</h3>
                <button onclick="closeTransactionModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <!-- Navigation par onglets -->
                <div class="border-b border-gray-200 mb-6">
                    <nav class="flex space-x-4" role="tablist">
                        <button type="button" onclick="switchTransactionTab('main-info')" id="tab-transaction-main-info" class="transaction-tab py-3 px-4 border-b-2 border-blue-500 text-blue-600 font-semibold text-sm">
                            <i class="fas fa-info-circle mr-2"></i>1. Informations principales
                        </button>
                        <button type="button" onclick="switchTransactionTab('banking-info')" id="tab-transaction-banking-info" class="transaction-tab py-3 px-4 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm">
                            <i class="fas fa-university mr-2"></i>2. Infos bancaires
                        </button>
                        <button type="button" onclick="switchTransactionTab('documents')" id="tab-transaction-documents" class="transaction-tab py-3 px-4 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm">
                            <i class="fas fa-file-alt mr-2"></i>3. Documents
                        </button>
                        <button type="button" onclick="switchTransactionTab('management')" id="tab-transaction-management" class="transaction-tab py-3 px-4 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm">
                            <i class="fas fa-cog mr-2"></i>4. Gestion & Actions
                        </button>
                    </nav>
                </div>

                <form id="transactionForm">
                    <!-- Onglet 1: Informations principales -->
                    <div id="content-main-info" class="transaction-tab-content">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Type *</label>
                                <select id="transaction-type" required class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="recette">💰 Recette</option>
                                    <option value="depense">💸 Dépense</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Compte *</label>
                                <select id="transaction-account" required class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Sélectionner un compte</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Description *</label>
                                <input type="text" id="transaction-description" required class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Description de la transaction">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Montant (€) *</label>
                                <input type="number" id="transaction-amount" step="0.01" required class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="0.00" onchange="updateTransactionSummary()">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date</label>
                                <input type="date" id="transaction-date" class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateTransactionSummary()">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Catégorie</label>
                                <select id="transaction-category" class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateTransactionSummary()">
                                    <option value="">Aucune catégorie</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tiers</label>
                                <select id="transaction-tiers" class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateTransactionSummary()">
                                    <option value="">Aucun tiers</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Référence</label>
                                <input type="text" id="transaction-reference" class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="N° facture, N° reçu...">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Mode de paiement</label>
                                <select id="transaction-payment-method" class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateTransactionSummary()">
                                    <option value="">-- Sélectionner --</option>
                                    <option value="especes">💵 Espèces</option>
                                    <option value="cheque">📝 Chèque</option>
                                    <option value="virement">🏦 Virement bancaire</option>
                                    <option value="carte">💳 Carte bancaire</option>
                                    <option value="prelevement">🔄 Prélèvement automatique</option>
                                    <option value="sepa">🇪🇺 Virement SEPA</option>
                                    <option value="tip">📋 TIP (Titre Interbancaire)</option>
                                    <option value="lcr">📄 Lettre de change</option>
                                    <option value="paypal">💎 PayPal / Paiement électronique</option>
                                    <option value="autre">⚙️ Autre</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea id="transaction-notes" rows="3" class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Notes additionnelles..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet 2: Informations bancaires -->
                    <div id="content-banking-info" class="transaction-tab-content" style="display: none;">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">État bancaire</label>
                                <select id="transaction-bank-status" class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="pending">⏳ En attente</option>
                                    <option value="cleared">✅ Encaissé / Payé</option>
                                    <option value="rejected">❌ Rejeté</option>
                                    <option value="cancelled">🚫 Annulé</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date de valeur</label>
                                <input type="date" id="transaction-value-date" class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Date valorisation bancaire</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date d'effet réelle</label>
                                <input type="date" id="transaction-effective-date" class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Date réelle débit/crédit sur compte</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Échéance (optionnel)</label>
                                <input type="date" id="transaction-due-date" class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Pour les paiements différés</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Impact sur le solde</label>
                                <input type="text" id="transaction-balance-impact" readonly class="mt-1 block w-full border rounded-lg px-3 py-2 bg-gray-100 text-gray-700 font-semibold" value="-0.00 €">
                                <p class="text-xs text-gray-500 mt-1">Calculé automatiquement</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Notes bancaires</label>
                                <textarea id="transaction-bank-notes" rows="3" class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Observations internes, numéro de chèque, etc..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet 3: Documents -->
                    <div id="content-documents" class="transaction-tab-content" style="display: none;">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <div>
                                    <h4 class="font-semibold text-blue-900"><i class="fas fa-paperclip mr-2"></i>Pièces justificatives</h4>
                                    <p class="text-sm text-blue-700 mt-1">Joignez des factures, reçus ou autres documents</p>
                                </div>
                                <button type="button" onclick="triggerFileUpload()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    <i class="fas fa-plus mr-2"></i>Joindre un document
                                </button>
                            </div>
                            <input type="file" id="transaction-file-input" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" style="display: none;" onchange="handleFileSelection()">
                            
                            <!-- Liste des documents joints -->
                            <div id="documents-list" class="space-y-2">
                                <p class="text-gray-500 text-center py-8 italic">Aucun document joint pour le moment</p>
                            </div>

                            <!-- Note d'aide -->
                            <div class="mt-4 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                <p class="text-xs text-gray-600">
                                    <i class="fas fa-info-circle mr-1 text-blue-500"></i>
                                    <strong>Formats acceptés :</strong> PDF, Images (JPG, PNG), Documents Word. Taille max : 10 Mo par fichier.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet 4: Gestion & Actions -->
                    <div id="content-management" class="transaction-tab-content" style="display: none;">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Panel gauche : Actions -->
                            <div class="space-y-4">
                                <h4 class="font-semibold text-gray-800 mb-3"><i class="fas fa-bolt mr-2 text-yellow-500"></i>Actions rapides</h4>
                                
                                <button type="button" onclick="duplicateTransaction()" class="w-full flex items-center justify-between p-3 border-2 border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-all">
                                    <div class="flex items-center">
                                        <i class="fas fa-copy text-blue-600 text-xl mr-3"></i>
                                        <div class="text-left">
                                            <p class="font-medium text-gray-800">Dupliquer cette transaction</p>
                                            <p class="text-xs text-gray-500">Créer une copie identique</p>
                                        </div>
                                    </div>
                                    <i class="fas fa-chevron-right text-gray-400"></i>
                                </button>

                                <button type="button" onclick="scheduleRecurrence()" class="w-full flex items-center justify-between p-3 border-2 border-gray-300 rounded-lg hover:border-green-500 hover:bg-green-50 transition-all">
                                    <div class="flex items-center">
                                        <i class="fas fa-redo-alt text-green-600 text-xl mr-3"></i>
                                        <div class="text-left">
                                            <p class="font-medium text-gray-800">Programmer une récurrence</p>
                                            <p class="text-xs text-gray-500">Automatiser cette opération</p>
                                        </div>
                                    </div>
                                    <i class="fas fa-chevron-right text-gray-400"></i>
                                </button>

                                <button type="button" onclick="deleteTransaction()" class="w-full flex items-center justify-between p-3 border-2 border-gray-300 rounded-lg hover:border-red-500 hover:bg-red-50 transition-all">
                                    <div class="flex items-center">
                                        <i class="fas fa-trash-alt text-red-600 text-xl mr-3"></i>
                                        <div class="text-left">
                                            <p class="font-medium text-gray-800">Supprimer / Annuler</p>
                                            <p class="text-xs text-gray-500">Effacer cette transaction</p>
                                        </div>
                                    </div>
                                    <i class="fas fa-chevron-right text-gray-400"></i>
                                </button>

                                <!-- Commentaires généraux -->
                                <div class="mt-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-comment-dots mr-2"></i>Commentaires généraux
                                    </label>
                                    <textarea id="transaction-general-comments" rows="4" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Remarques internes, contexte, etc..."></textarea>
                                </div>
                            </div>

                            <!-- Panel droit : Récapitulatif -->
                            <div>
                                <div class="sticky top-4">
                                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-300 rounded-lg p-4">
                                        <h4 class="font-semibold text-blue-900 mb-4 text-center">
                                            <i class="fas fa-clipboard-check mr-2"></i>Récapitulatif de l'opération
                                        </h4>
                                        
                                        <div class="space-y-3 text-sm">
                                            <div class="flex justify-between items-center p-2 bg-white rounded">
                                                <span class="text-gray-600">Type</span>
                                                <span id="summary-type" class="font-semibold text-gray-900">-</span>
                                            </div>
                                            <div class="flex justify-between items-center p-2 bg-white rounded">
                                                <span class="text-gray-600">Compte</span>
                                                <span id="summary-account" class="font-semibold text-gray-900">-</span>
                                            </div>
                                            <div class="flex justify-between items-center p-2 bg-white rounded">
                                                <span class="text-gray-600">Montant</span>
                                                <span id="summary-amount" class="font-bold text-xl text-blue-900">0.00 €</span>
                                            </div>
                                            <div class="flex justify-between items-center p-2 bg-white rounded">
                                                <span class="text-gray-600">Date</span>
                                                <span id="summary-date" class="font-semibold text-gray-900">-</span>
                                            </div>
                                            <div class="flex justify-between items-center p-2 bg-white rounded">
                                                <span class="text-gray-600">Référence</span>
                                                <span id="summary-reference" class="font-semibold text-gray-900">-</span>
                                            </div>
                                            <div class="flex justify-between items-center p-2 bg-white rounded">
                                                <span class="text-gray-600">Mode de paiement</span>
                                                <span id="summary-payment-method" class="font-semibold text-gray-900">-</span>
                                            </div>
                                            <div class="flex justify-between items-center p-2 bg-white rounded">
                                                <span class="text-gray-600">Catégorie</span>
                                                <span id="summary-category" class="font-semibold text-gray-900">-</span>
                                            </div>
                                            <div class="flex justify-between items-center p-2 bg-white rounded">
                                                <span class="text-gray-600">État bancaire</span>
                                                <span id="summary-bank-status" class="font-semibold text-gray-900">⏳ En attente</span>
                                            </div>
                                            <div class="flex justify-between items-center p-2 bg-white rounded">
                                                <span class="text-gray-600">Date de valeur</span>
                                                <span id="summary-value-date" class="font-semibold text-gray-900">-</span>
                                            </div>
                                        </div>

                                        <div class="mt-4 p-3 bg-blue-600 text-white rounded-lg text-center">
                                            <p class="text-xs mb-1">Impact sur le solde</p>
                                            <p id="summary-impact" class="text-2xl font-bold">-0.00 €</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Boutons de validation (toujours visibles) -->
                    <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-200">
                        <button type="button" onclick="closeTransactionModal()" class="px-4 py-2 text-gray-600 border rounded-lg hover:bg-gray-50">
                            <i class="fas fa-times mr-2"></i>Annuler
                        </button>
                        <button type="button" onclick="saveTransaction(false)" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                            <i class="fas fa-save mr-2"></i>Valider et fermer
                        </button>
                        <button type="button" onclick="saveTransaction(true)" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                            <i class="fas fa-plus mr-2"></i>Ajouter et continuer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Transfer Modal -->
    <div id="transferModal" class="modal">
        <div class="modal-content" style="max-width: 1200px; max-height: 90vh; overflow-y: auto;">
            <div class="modal-header">
                <h3 class="text-lg font-semibold"><i class="fas fa-exchange-alt mr-2 text-purple-600"></i>Virement de Fonds</h3>
                <button onclick="closeTransferModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <!-- Navigation des onglets -->
                <div class="border-b border-gray-200 mb-6">
                    <nav class="flex space-x-4">
                        <button type="button" onclick="switchTransferTab('main-info')" id="transfer-tab-main-info" class="transfer-tab py-3 px-4 border-b-2 border-purple-500 text-purple-600 font-medium text-sm">
                            <i class="fas fa-info-circle mr-2"></i>Informations principales
                        </button>
                        <button type="button" onclick="switchTransferTab('bank-info')" id="transfer-tab-bank-info" class="transfer-tab py-3 px-4 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm">
                            <i class="fas fa-university mr-2"></i>Infos bancaires
                        </button>
                        <button type="button" onclick="switchTransferTab('documents')" id="transfer-tab-documents" class="transfer-tab py-3 px-4 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm">
                            <i class="fas fa-folder-open mr-2"></i>Documents
                        </button>
                        <button type="button" onclick="switchTransferTab('management')" id="transfer-tab-management" class="transfer-tab py-3 px-4 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm">
                            <i class="fas fa-cog mr-2"></i>Gestion & Actions
                        </button>
                    </nav>
                </div>

                <form id="transferForm">
                    <!-- Onglet 1: Informations principales -->
                    <div id="transfer-content-main-info" class="transfer-tab-content">
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><i class="fas fa-arrow-circle-up text-red-500 mr-2"></i>Compte source *</label>
                                    <select id="transfer-from-account" required class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                        <option value="">Sélectionner un compte</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><i class="fas fa-arrow-circle-down text-green-500 mr-2"></i>Compte destination *</label>
                                    <select id="transfer-to-account" required class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                        <option value="">Sélectionner un compte</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700"><i class="fas fa-euro-sign text-purple-600 mr-2"></i>Montant (€) *</label>
                                <input type="number" id="transfer-amount" step="0.01" required class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700"><i class="fas fa-align-left mr-2"></i>Description</label>
                                <input type="text" id="transfer-description" placeholder="Virement de fonds" class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700"><i class="fas fa-calendar-alt mr-2"></i>Date du virement</label>
                                <input type="date" id="transfer-date" class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>
                    </div>

                    <!-- Onglet 2: Infos bancaires -->
                    <div id="transfer-content-bank-info" class="transfer-tab-content hidden">
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><i class="fas fa-hashtag mr-2"></i>Référence virement</label>
                                    <input type="text" id="transfer-reference" placeholder="Référence unique" class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><i class="fas fa-credit-card mr-2"></i>Mode de paiement</label>
                                    <select id="transfer-payment-method" class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                        <option value="">Sélectionner...</option>
                                        <option value="virement">🏛️ Virement bancaire</option>
                                        <option value="sepa">🇪🇺 Virement SEPA</option>
                                        <option value="carte">💳 Carte bancaire</option>
                                        <option value="cheque">📋 Chèque</option>
                                        <option value="especes">💵 Espèces</option>
                                        <option value="prelevement">🔄 Prélèvement</option>
                                        <option value="autre">⚙️ Autre</option>
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><i class="fas fa-calendar-check mr-2"></i>Date de valeur</label>
                                    <input type="date" id="transfer-value-date" class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><i class="fas fa-calendar-day mr-2"></i>Date d'exécution</label>
                                    <input type="date" id="transfer-execution-date" class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><i class="fas fa-tasks mr-2"></i>Statut</label>
                                    <select id="transfer-status" class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                        <option value="pending">⏳ En attente</option>
                                        <option value="cleared">✅ Validé / Effectué</option>
                                        <option value="rejected">❌ Rejeté</option>
                                        <option value="cancelled">🚫 Annulé</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700"><i class="fas fa-sticky-note mr-2"></i>Notes bancaires</label>
                                <textarea id="transfer-bank-notes" rows="3" placeholder="Informations bancaires supplémentaires..." class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet 3: Documents -->
                    <div id="transfer-content-documents" class="transfer-tab-content hidden">
                        <div class="space-y-4">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <h4 class="font-semibold text-blue-900 mb-2"><i class="fas fa-paperclip mr-2"></i>Pièces jointes</h4>
                                <p class="text-sm text-blue-700 mb-3">Ajoutez des documents justificatifs (ordres de virement, confirmations, etc.)</p>
                                <input type="file" id="transfer-file-input" multiple class="hidden" onchange="handleTransferFiles(event)">
                                <button type="button" onclick="document.getElementById('transfer-file-input').click()" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                                    <i class="fas fa-upload mr-2"></i>Ajouter des fichiers
                                </button>
                            </div>
                            <div id="transfer-files-list" class="space-y-2">
                                <!-- Liste des fichiers ajoutés -->
                            </div>
                        </div>
                    </div>

                    <!-- Onglet 4: Gestion & Actions -->
                    <div id="transfer-content-management" class="transfer-tab-content hidden">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Panel gauche : Actions -->
                            <div class="space-y-3">
                                <h4 class="font-semibold text-gray-800 mb-4"><i class="fas fa-bolt mr-2 text-purple-600"></i>Actions rapides</h4>
                                
                                <button type="button" onclick="duplicateTransfer()" class="w-full flex items-center justify-between p-3 border-2 border-gray-300 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition-all">
                                    <div class="flex items-center">
                                        <i class="fas fa-copy text-purple-600 text-xl mr-3"></i>
                                        <div class="text-left">
                                            <p class="font-medium text-gray-800">Dupliquer</p>
                                            <p class="text-xs text-gray-500">Créer un virement similaire</p>
                                        </div>
                                    </div>
                                    <i class="fas fa-chevron-right text-gray-400"></i>
                                </button>

                                <button type="button" onclick="scheduleTransfer()" class="w-full flex items-center justify-between p-3 border-2 border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-all">
                                    <div class="flex items-center">
                                        <i class="fas fa-clock text-blue-600 text-xl mr-3"></i>
                                        <div class="text-left">
                                            <p class="font-medium text-gray-800">Programmer</p>
                                            <p class="text-xs text-gray-500">Planifier pour plus tard</p>
                                        </div>
                                    </div>
                                    <i class="fas fa-chevron-right text-gray-400"></i>
                                </button>

                                <button type="button" onclick="cancelTransfer()" class="w-full flex items-center justify-between p-3 border-2 border-gray-300 rounded-lg hover:border-red-500 hover:bg-red-50 transition-all">
                                    <div class="flex items-center">
                                        <i class="fas fa-ban text-red-600 text-xl mr-3"></i>
                                        <div class="text-left">
                                            <p class="font-medium text-gray-800">Annuler</p>
                                            <p class="text-xs text-gray-500">Annuler le virement</p>
                                        </div>
                                    </div>
                                    <i class="fas fa-chevron-right text-gray-400"></i>
                                </button>

                                <!-- Commentaires généraux -->
                                <div class="mt-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-comment-dots mr-2"></i>Commentaires généraux
                                    </label>
                                    <textarea id="transfer-general-comments" rows="4" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="Remarques internes, contexte, etc..."></textarea>
                                </div>
                            </div>

                            <!-- Panel droit : Récapitulatif -->
                            <div>
                                <div class="sticky top-4">
                                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-300 rounded-lg p-4">
                                        <h4 class="font-semibold text-purple-900 mb-4 text-center">
                                            <i class="fas fa-clipboard-check mr-2"></i>Récapitulatif du virement
                                        </h4>
                                        
                                        <div class="space-y-3 text-sm">
                                            <div class="flex justify-between items-center p-2 bg-white rounded">
                                                <span class="text-gray-600">Compte source</span>
                                                <span id="transfer-summary-from" class="font-semibold text-gray-900">-</span>
                                            </div>
                                            <div class="flex justify-between items-center p-2 bg-white rounded">
                                                <span class="text-gray-600">Compte destination</span>
                                                <span id="transfer-summary-to" class="font-semibold text-gray-900">-</span>
                                            </div>
                                            <div class="flex justify-between items-center p-2 bg-white rounded">
                                                <span class="text-gray-600">Montant</span>
                                                <span id="transfer-summary-amount" class="font-bold text-xl text-purple-900">0.00 €</span>
                                            </div>
                                            <div class="flex justify-between items-center p-2 bg-white rounded">
                                                <span class="text-gray-600">Date</span>
                                                <span id="transfer-summary-date" class="font-semibold text-gray-900">-</span>
                                            </div>
                                            <div class="flex justify-between items-center p-2 bg-white rounded">
                                                <span class="text-gray-600">Référence</span>
                                                <span id="transfer-summary-reference" class="font-semibold text-gray-900">-</span>
                                            </div>
                                            <div class="flex justify-between items-center p-2 bg-white rounded">
                                                <span class="text-gray-600">Statut</span>
                                                <span id="transfer-summary-status" class="font-semibold text-gray-900">⏳ En attente</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Boutons de validation (toujours visibles) -->
                    <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-200">
                        <button type="button" onclick="closeTransferModal()" class="px-4 py-2 text-gray-600 border rounded-lg hover:bg-gray-50">
                            <i class="fas fa-times mr-2"></i>Annuler
                        </button>
                        <button type="button" onclick="saveTransfer()" class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600">
                            <i class="fas fa-check mr-2"></i>Effectuer le virement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Account Modal -->
    <div id="accountModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="accountModalTitle" class="text-lg font-semibold">Nouveau Compte</h3>
                <button onclick="closeAccountModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="accountForm">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nom *</label>
                        <input type="text" id="account-name" required class="mt-1 block w-full border rounded-lg px-3 py-2">
                    </div>
                    <div id="bank-field" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700">Banque</label>
                        <input type="text" id="account-bank" class="mt-1 block w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Solde initial (€)</label>
                        <input type="number" id="account-balance" step="0.01" value="0" class="mt-1 block w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="account-description" rows="3" class="mt-1 block w-full border rounded-lg px-3 py-2"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="closeAccountModal()" class="px-4 py-2 text-gray-600 border rounded-lg hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="button" onclick="saveAccount()" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                        Enregistrer
                    </button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tiers Modal -->
    <div id="tiersModal" class="modal">
        <div class="modal-content" style="max-width: 900px; max-height: 90vh; overflow-y: auto;">
            <div class="modal-header">
                <h3 id="tiersModalTitle" class="text-lg font-semibold">Nouveau Tiers</h3>
                <button onclick="closeTiersModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <!-- Onglets Navigation -->
                <div class="border-b border-gray-200 mb-4">
                    <nav class="flex space-x-6">
                        <button onclick="switchTiersTab('identite')" id="tab-identite" class="tiers-tab py-2 px-1 border-b-2 border-blue-500 text-blue-600 font-medium text-sm">
                            🧾 Identité
                        </button>
                        <button onclick="switchTiersTab('generales')" id="tab-generales" class="tiers-tab py-2 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm">
                            🏠 Infos générales
                        </button>
                        <button onclick="switchTiersTab('contact')" id="tab-contact" class="tiers-tab py-2 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm">
                            👤 Contact
                        </button>
                        <button onclick="switchTiersTab('legal')" id="tab-legal" class="tiers-tab py-2 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm">
                            💼 Légal
                        </button>
                        <button onclick="switchTiersTab('compta')" id="tab-compta" class="tiers-tab py-2 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm">
                            💳 Comptabilité
                        </button>
                        <button onclick="switchTiersTab('dates')" id="tab-dates" class="tiers-tab py-2 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm">
                            🗓️ Dates
                        </button>
                        <button onclick="switchTiersTab('autres')" id="tab-autres" class="tiers-tab py-2 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm">
                            📝 Autres
                        </button>
                    </nav>
                </div>

                <form id="tiersForm">
                    <!-- Onglet 1: Identité -->
                    <div id="content-identite" class="tiers-tab-content">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Code client</label>
                                <input type="text" id="tiers-code" class="mt-1 block w-full border rounded-lg px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Famille</label>
                                <input type="text" id="tiers-famille" class="mt-1 block w-full border rounded-lg px-3 py-2">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Raison sociale / Nom complet *</label>
                                <input type="text" id="tiers-raison-sociale" required class="mt-1 block w-full border rounded-lg px-3 py-2">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Adresse principale</label>
                                <textarea id="tiers-adresse" rows="3" class="mt-1 block w-full border rounded-lg px-3 py-2"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Téléphone principal</label>
                                <input type="tel" id="tiers-telephone" class="mt-1 block w-full border rounded-lg px-3 py-2">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Note interne</label>
                                <textarea id="tiers-note-interne" rows="3" class="mt-1 block w-full border rounded-lg px-3 py-2"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet 2: Informations générales -->
                    <div id="content-generales" class="tiers-tab-content hidden">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Référence</label>
                                <input type="text" id="tiers-reference" class="mt-1 block w-full border rounded-lg px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Type de client</label>
                                <select id="tiers-type" class="mt-1 block w-full border rounded-lg px-3 py-2">
                                    <option value="">Sélectionner...</option>
                                    <option value="particulier">Particulier</option>
                                    <option value="societe">Société</option>
                                    <option value="fournisseur">Fournisseur</option>
                                    <option value="autre">Autre</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Statut</label>
                                <select id="tiers-statut" class="mt-1 block w-full border rounded-lg px-3 py-2">
                                    <option value="actif">Actif</option>
                                    <option value="bloque">Bloqué</option>
                                    <option value="special">Spécial</option>
                                    <option value="groupe">Groupe</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Code postal</label>
                                <input type="text" id="tiers-code-postal" class="mt-1 block w-full border rounded-lg px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Ville</label>
                                <input type="text" id="tiers-ville" class="mt-1 block w-full border rounded-lg px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Wilaya / Région</label>
                                <input type="text" id="tiers-wilaya" class="mt-1 block w-full border rounded-lg px-3 py-2">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Adresse de livraison</label>
                                <textarea id="tiers-adresse-livraison" rows="3" class="mt-1 block w-full border rounded-lg px-3 py-2"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet 3: Contact -->
                    <div id="content-contact" class="tiers-tab-content hidden">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Correspondant</label>
                                <input type="text" id="tiers-contact" class="mt-1 block w-full border rounded-lg px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Téléphone fixe</label>
                                <input type="tel" id="tiers-telephone-fixe" class="mt-1 block w-full border rounded-lg px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Mobile</label>
                                <input type="tel" id="tiers-mobile" class="mt-1 block w-full border rounded-lg px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Fax</label>
                                <input type="tel" id="tiers-fax" class="mt-1 block w-full border rounded-lg px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">E-mail</label>
                                <input type="email" id="tiers-email" class="mt-1 block w-full border rounded-lg px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Site web</label>
                                <input type="url" id="tiers-site-web" class="mt-1 block w-full border rounded-lg px-3 py-2">
                            </div>
                        </div>
                    </div>

                    <!-- Onglet 4: Informations légales -->
                    <div id="content-legal" class="tiers-tab-content hidden">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Identifiant fiscal</label>
                                <input type="text" id="tiers-identifiant-fiscal" class="mt-1 block w-full border rounded-lg px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">NIS</label>
                                <input type="text" id="tiers-nis" class="mt-1 block w-full border rounded-lg px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Registre du commerce (RC)</label>
                                <input type="text" id="tiers-siret" class="mt-1 block w-full border rounded-lg px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Article d'imposition</label>
                                <input type="text" id="tiers-article-imposition" class="mt-1 block w-full border rounded-lg px-3 py-2">
                            </div>
                        </div>
                    </div>

                    <!-- Onglet 5: Comptabilité -->
                    <div id="content-compta" class="tiers-tab-content hidden">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Code comptable</label>
                                <input type="text" id="tiers-code-comptable" class="mt-1 block w-full border rounded-lg px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Numéro de compte</label>
                                <input type="text" id="tiers-numero-compte" class="mt-1 block w-full border rounded-lg px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">RIB / IBAN</label>
                                <input type="text" id="tiers-rib" class="mt-1 block w-full border rounded-lg px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Solde actuel</label>
                                <input type="number" step="0.01" id="tiers-solde-actuel" class="mt-1 block w-full border rounded-lg px-3 py-2" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">TVA</label>
                                <select id="tiers-exoneration-tva" class="mt-1 block w-full border rounded-lg px-3 py-2">
                                    <option value="non">Non exonéré</option>
                                    <option value="oui">Exonéré</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Mode de paiement</label>
                                <select id="tiers-mode-paiement" class="mt-1 block w-full border rounded-lg px-3 py-2">
                                    <option value="">Sélectionner...</option>
                                    <option value="especes">Espèces</option>
                                    <option value="cheque">Chèque</option>
                                    <option value="virement">Virement</option>
                                    <option value="carte">Carte bancaire</option>
                                    <option value="autre">Autre</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Conditions d'échéance</label>
                                <input type="text" id="tiers-conditions-echeance" class="mt-1 block w-full border rounded-lg px-3 py-2" placeholder="Ex: 30 jours fin de mois">
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Dates importantes -->
                    <div id="content-dates" class="tiers-tab-content hidden">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date de création</label>
                                <input type="date" id="tiers-date-creation" class="mt-1 block w-full border rounded-lg px-3 py-2" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Dernière modification</label>
                                <input type="date" id="tiers-date-modification" class="mt-1 block w-full border rounded-lg px-3 py-2" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date 1 (libre)</label>
                                <input type="date" id="tiers-date1" class="mt-1 block w-full border rounded-lg px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date 2 (libre)</label>
                                <input type="date" id="tiers-date2" class="mt-1 block w-full border rounded-lg px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date 3 (libre)</label>
                                <input type="date" id="tiers-date3" class="mt-1 block w-full border rounded-lg px-3 py-2">
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Autres informations -->
                    <div id="content-autres" class="tiers-tab-content hidden">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Remarques</label>
                                <textarea id="tiers-notes" rows="4" class="mt-1 block w-full border rounded-lg px-3 py-2"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Mots-clés</label>
                                <input type="text" id="tiers-mots-cles" class="mt-1 block w-full border rounded-lg px-3 py-2" placeholder="Séparés par des virgules">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Solvabilité</label>
                                <select id="tiers-solvabilite" class="mt-1 block w-full border rounded-lg px-3 py-2">
                                    <option value="">Non évaluée</option>
                                    <option value="excellente">Excellente</option>
                                    <option value="bonne">Bonne</option>
                                    <option value="moyenne">Moyenne</option>
                                    <option value="faible">Faible</option>
                                    <option value="risquee">Risquée</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                        <button type="button" onclick="closeTiersModal()" class="px-4 py-2 text-gray-600 border rounded-lg hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="button" onclick="saveTiers()" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Opération de Caisse -->
    <div id="caisseOperationModal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3 class="text-lg font-semibold">
                    <i class="fas fa-cash-register mr-2 text-orange-600"></i>Nouvelle Opération de Caisse
                </h3>
                <button onclick="closeCaisseOperationModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="caisseOperationForm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Caisse *</label>
                            <select id="caisse-operation-caisse" required class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                <option value="">Sélectionner une caisse</option>
                                <option value="caisse_principale">Caisse Principale</option>
                                <option value="petite_caisse">Petite Caisse</option>
                                <option value="caisse_secondaire">Caisse Secondaire</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Type d'opération *</label>
                            <select id="caisse-operation-type" required class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                <option value="">Sélectionner le type</option>
                                <option value="recette">💰 Recette (Encaissement)</option>
                                <option value="depense">💸 Dépense (Décaissement)</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Description *</label>
                            <input type="text" id="caisse-operation-description" required class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500" placeholder="Ex: Vente produits, Achat fournitures...">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Montant (€) *</label>
                            <input type="number" id="caisse-operation-amount" step="0.01" min="0" required class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500" placeholder="0.00">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date *</label>
                            <input type="date" id="caisse-operation-date" required class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Référence</label>
                            <input type="text" id="caisse-operation-reference" class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500" placeholder="N° facture, N° reçu...">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Catégorie</label>
                            <select id="caisse-operation-category" class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                <option value="">Aucune catégorie</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tiers concerné</label>
                            <select id="caisse-operation-tiers" class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                <option value="">Aucun tiers</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Notes / Commentaires</label>
                            <textarea id="caisse-operation-notes" rows="3" class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500" placeholder="Informations complémentaires..."></textarea>
                        </div>
                    </div>
                    
                    <!-- Résumé de l'opération -->
                    <div id="operation-summary" class="mt-6 p-4 bg-orange-50 border border-orange-200 rounded-lg" style="display: none;">
                        <h4 class="text-sm font-semibold text-orange-800 mb-2">
                            <i class="fas fa-info-circle mr-2"></i>Résumé de l'opération
                        </h4>
                        <div class="text-sm text-orange-700">
                            <p><strong>Caisse:</strong> <span id="summary-caisse">-</span></p>
                            <p><strong>Type:</strong> <span id="summary-type">-</span></p>
                            <p><strong>Montant:</strong> <span id="summary-amount">-</span></p>
                            <p><strong>Impact sur le solde:</strong> <span id="summary-impact">-</span></p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-6" id="caisse-modal-buttons">
                        <button type="button" onclick="closeCaisseOperationModal()" class="px-4 py-2 text-gray-600 border rounded-lg hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="button" id="caisse-validate-button" onclick="saveCaisseOperation(false)" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600" style="display: inline-block !important; visibility: visible !important; opacity: 1 !important; position: relative !important; z-index: 9999 !important;">
                            <i class="fas fa-save mr-2"></i>Valider et fermer
                        </button>
                        <button type="button" id="caisse-continue-button" onclick="saveCaisseOperation(true)" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600" style="display: inline-block !important;">
                            <i class="fas fa-plus mr-2"></i>Ajouter et continuer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Nouvel Achat avec Onglets -->
    <div id="achatModal" class="modal" style="display: none;">
        <div class="modal-content large-modal">
            <div class="modal-header">
                <h2><i class="fas fa-shopping-cart"></i> Nouvel Achat</h2>
                <span class="modal-close" onclick="closeAchatModal()">&times;</span>
            </div>

            <!-- Navigation par onglets -->
            <nav class="modal-tabs">
                <button class="tab-btn active" onclick="switchAchatTab('main-info')">
                    <i class="fas fa-info-circle"></i>1. Infos principales
                </button>
                <button class="tab-btn" onclick="switchAchatTab('financial')">
                    <i class="fas fa-calculator"></i>2. Détails financiers
                </button>
                <button class="tab-btn" onclick="switchAchatTab('payment')">
                    <i class="fas fa-credit-card"></i>3. Paiement & État
                </button>
                <button class="tab-btn" onclick="switchAchatTab('documents')">
                    <i class="fas fa-file-alt"></i>4. Documents & Notes
                </button>
                <button class="tab-btn" onclick="switchAchatTab('management')">
                    <i class="fas fa-cogs"></i>5. Gestion & Actions
                </button>
            </nav>

            <form id="formNouvelAchat" onsubmit="saveAchatFromForm(event)">
                <div class="modal-body">
                    
                    <!-- ONGLET 1: Informations principales -->
                    <div id="tab-main-info" class="tab-content active">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Fournisseur *</label>
                                <select id="achat-fournisseur" name="fournisseur" required>
                                    <option value="">Sélectionner un fournisseur</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Catégorie pièces *</label>
                                <select id="achat-categorie-pieces" name="categorie_pieces" required>
                                    <option value="">Sélectionner une catégorie</option>
                                    <option value="FOURNITURE">Fournitures</option>
                                    <option value="EQUIPEMENT">Équipement</option>
                                    <option value="MAINTENANCE">Maintenance</option>
                                    <option value="SERVICES">Services</option>
                                    <option value="TRAVAUX">Travaux</option>
                                    <option value="CONSOMMABLE">Consommables</option>
                                    <option value="AUTRES">Autres</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Référence *</label>
                                <input type="text" id="achat-reference" placeholder="Référence facture/commande" required>
                            </div>
                            <div class="form-group">
                                <label>Montant TTC *</label>
                                <input type="number" step="0.01" id="achat-montant-ttc" placeholder="0,00" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Description de l'achat *</label>
                            <textarea id="achat-description" rows="3" placeholder="Description détaillée de l'achat" required></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Date de facture *</label>
                                <input type="date" id="achat-date-facture" required>
                            </div>
                            <div class="form-group">
                                <label>Date d'échéance</label>
                                <input type="date" id="achat-date-echeance">
                            </div>
                        </div>

                        <div class="summary-box">
                            <h4>Récapitulatif</h4>
                            <div class="summary-content">
                                <span id="recap-fournisseur">Fournisseur: -</span>
                                <span id="recap-reference">Référence: -</span>
                                <span id="recap-categorie">Catégorie: -</span>
                                <span id="recap-ttc">TTC: 0,00 €</span>
                                <span id="recap-date">Date: -</span>
                            </div>
                        </div>
                    </div>

                    <!-- ONGLET 2: Détails financiers -->
                    <div id="tab-financial" class="tab-content">
                        <div class="info-box">
                            <h4>💰 Informations financières calculées</h4>
                            <p>Le montant TTC est saisi dans l'onglet 1. Les calculs ci-dessous sont mis à jour automatiquement.</p>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Montant HT (calculé)</label>
                                <input type="number" step="0.01" id="achat-montant-ht" readonly>
                            </div>
                            <div class="form-group">
                                <label>Montant TTC (de l'onglet 1)</label>
                                <input type="number" step="0.01" id="achat-montant-ttc-display" readonly>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Taux TVA (%)</label>
                                <input type="number" step="0.01" id="achat-taux-tva" 
                                       onchange="calculateFinancialsFromTTC()" value="20">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Remise</label>
                                <input type="number" step="0.01" id="achat-remise" 
                                       onchange="calculateFinancials()" value="0">
                            </div>
                            <div class="form-group">
                                <label>Timbre</label>
                                <input type="number" step="0.01" id="achat-timbre" 
                                       onchange="calculateFinancials()" value="0">
                            </div>
                        </div>

                        <div class="calculation-summary">
                            <div class="calc-item">
                                <label>TVA:</label>
                                <span id="achat-tva-calcule">0,00 €</span>
                            </div>
                            <div class="calc-item total">
                                <label>Total TTC:</label>
                                <span id="achat-ttc-calcule">0,00 €</span>
                            </div>
                        </div>
                    </div>

                    <!-- ONGLET 3: Paiement & État -->
                    <div id="tab-payment" class="tab-content">
                        <!-- Récapitulatif du montant total -->
                        <div class="payment-summary-card">
                            <div class="payment-summary-header">
                                <h3><i class="fas fa-euro-sign"></i> Montant Total</h3>
                                <div class="total-amount">
                                    <span id="achat-montant-total-display">0,00 €</span>
                                </div>
                            </div>
                            <div class="payment-summary-details">
                                <div class="summary-line">
                                    <span>Montant HT:</span>
                                    <span id="achat-montant-ht-display">0,00 €</span>
                                </div>
                                <div class="summary-line">
                                    <span>TVA:</span>
                                    <span id="achat-tva-display">0,00 €</span>
                                </div>
                                <div class="summary-line">
                                    <span>Total TTC:</span>
                                    <span id="achat-total-ttc-display" class="font-bold">0,00 €</span>
                                </div>
                            </div>
                        </div>

                        <!-- Section État de paiement -->
                        <div class="payment-status-section">
                            <h4><i class="fas fa-flag"></i> État de Paiement</h4>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Statut *</label>
                                    <select id="achat-statut" onchange="updatePaymentStatus()" class="status-select">
                                        <option value="non_paye">🔴 Non payé</option>
                                        <option value="partiel">🟡 Partiel</option>
                                        <option value="paye">🟢 Payé</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Mode de paiement</label>
                                    <select id="achat-mode-paiement" name="mode_paiement" onchange="updatePaymentFields()">
                                        <option value="">Sélectionner</option>
                                        <option value="especes">💵 Espèces</option>
                                        <option value="carte_bancaire">💳 Carte bancaire</option>
                                        <option value="virement">🏦 Virement</option>
                                        <option value="cheque">📄 Chèque</option>
                                        <option value="prelevement">📥 Prélèvement</option>
                                        <option value="autre">⚙️ Autre</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Section Détails de paiement -->
                        <div class="payment-details-section" id="payment-details-section" style="display: none;">
                            <h4><i class="fas fa-credit-card"></i> Détails de Paiement</h4>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Montant payé *</label>
                                    <div class="input-with-unit">
                                        <input type="number" step="0.01" id="achat-montant-payé" name="montant_paye" 
                                               onchange="calculatePaymentBalance()" placeholder="0.00">
                                        <span class="unit">€</span>
                                    </div>
                                    <small class="help-text" id="payment-help"></small>
                                </div>
                                <div class="form-group">
                                    <label>Date de paiement *</label>
                                    <input type="date" id="achat-date-paiement" name="date_paiement" onchange="updatePaymentStatus()">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Compte / Caisse *</label>
                                    <select id="achat-compte" name="compte" onchange="updatePaymentStatus()">
                                        <option value="">Sélectionner un compte</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Référence paiement</label>
                                    <input type="text" id="achat-ref-paiement" 
                                           placeholder="Nº transaction, chèque...">
                                </div>
                            </div>

                            <!-- Section Note paiement -->
                            <div class="form-group">
                                <label>Note paiement</label>
                                <textarea id="achat-note-paiement" rows="2" 
                                          placeholder="Notes facultatives sur le paiement..."></textarea>
                            </div>
                        </div>

                        <!-- Affichage du solde restant -->
                        <div class="payment-balance-section" id="payment-balance-section" style="display: none;">
                            <div class="balance-card">
                                <h4><i class="fas fa-balance-scale"></i> Solde</h4>
                                <div class="balance-display">
                                    <div class="balance-item">
                                        <span>Montant à payer:</span>
                                        <span id="montant-total-affiche" class="amount">0,00 €</span>
                                    </div>
                                    <div class="balance-item">
                                        <span>Montant payé:</span>
                                        <span id="montant-payé-affiche" class="amount">0,00 €</span>
                                    </div>
                                    <div class="balance-item remainder">
                                        <span>Reste à payer:</span>
                                        <span id="reste-a-payer" class="amount">0,00 €</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Historique des paiements (si paiements partiels) -->
                        <div class="payment-history-section" id="payment-history-section" style="display: none;">
                            <h4><i class="fas fa-history"></i> Historique des Paiements</h4>
                            <div class="payment-history-container">
                                <div class="no-history" id="no-payment-history">
                                    <i class="fas fa-info-circle"></i>
                                    <p>Aucun paiement partiel enregistré pour ce achat.</p>
                                </div>
                                <div class="payment-history-list" id="payment-history-list" style="display: none;">
                                    <!-- Les paiements partiels seront affichés ici -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ONGLET 4: Documents & Notes -->
                    <div id="tab-documents" class="tab-content">
                        <div class="form-group">
                            <label>Documents joints</label>
                            <div class="upload-zone" id="upload-zone">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Glissez-déposez vos documents ici</p>
                                <input type="file" id="achat-documents" multiple style="display: none;">
                                <button type="button" onclick="document.getElementById('achat-documents').click()">
                                    Sélectionner des fichiers
                                </button>
                            </div>
                            <div id="uploaded-files"></div>
                        </div>

                        <div class="form-group">
                            <label>Notes internes</label>
                            <textarea id="achat-notes-internes" rows="3" 
                                      placeholder="Notes internes"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Mots-clés</label>
                            <input type="text" id="achat-mots-cles" 
                                   placeholder="Mots-clés séparés par des virgules">
                        </div>
                    </div>

                    <!-- ONGLET 5: Gestion & Actions -->
                    <div id="tab-management" class="tab-content">
                        <div class="form-group">
                            <label>Paramètres avancés</label>
                            <div class="checkbox-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" id="achat-auto-generate-ref">
                                    Générer automatiquement une référence
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Actions disponibles</label>
                            <div class="action-buttons">
                                <button type="button" onclick="duplicateCurrentAchat()">
                                    <i class="fas fa-copy"></i> Dupliquer
                                </button>
                                <button type="button" onclick="exportAchatData()">
                                    <i class="fas fa-download"></i> Exporter
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Boutons d'action en bas du modal -->
                <div class="modal-footer sticky-footer">
                    <div class="flex justify-between items-center">
                        <button type="button" class="btn btn-secondary" onclick="closeAchatModal()">
                            <i class="fas fa-times mr-2"></i>Annuler
                        </button>
                        <button type="submit" class="btn btn-primary btn-large">
                            <i class="fas fa-save mr-2"></i>Enregistrer & Fermer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Gestion Catégories - Version Corrigée -->
    <div id="categorieModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 800px; margin: 2% auto;">
            <div class="modal-header">
                <h2 style="font-size: 1.25rem !important;">
                    <i class="fas fa-tag me-2"></i><span id="modal-categorie-title">Nouvelle Catégorie</span>
                </h2>
                <span class="modal-close" onclick="closeCategoryModal()" style="font-size: 24px;">&times;</span>
            </div>
            
            <form id="form-categorie" class="p-6 space-y-6">
                <input type="hidden" id="categorie-id" name="id">
                
                <!-- Informations principales -->
                <div class="bg-gray-50 rounded-lg p-3">
                    <h5 class="font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>Informations Principales
                    </h5>
                    
                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <div class="form-group">
                            <label>Code <span class="text-red-500">*</span></label>
                            <input type="text" class="form-control" id="categorie-code" name="code" required maxlength="20" placeholder="FOURNITURE">
                            <p class="form-help text-xs">Code unique (max 20 caractères)</p>
                        </div>
                        
                        <div class="form-group">
                            <label>Nom <span class="text-red-500">*</span></label>
                            <input type="text" class="form-control" id="categorie-nom" name="nom" required maxlength="100" placeholder="Fournitures">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" id="categorie-description" name="description" rows="2" placeholder="Description détaillée de la catégorie..."></textarea>
                    </div>
                </div>
                
                <!-- Apparence -->
                <div style="background: #f8fafc; border-radius: 8px; padding: 1rem; margin: 1rem 0;">
                    <h5 style="font-weight: 600; color: #1f2937; margin-bottom: 1rem; display: flex; align-items: center;">
                        <i class="fas fa-palette text-purple-600 mr-2"></i>Apparence
                    </h5>
                    
                    <!-- Sélecteur d'icônes -->
                    <div style="margin-bottom: 0.5rem;">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Icône <span class="text-red-500">*</span></label>
                        <div class="flex items-center space-x-3">
                            <input type="hidden" id="categorie-icone" name="icone" value="fas fa-tag">
                            <div id="selected-icon" class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center text-lg text-blue-600">
                                <i class="fas fa-tag"></i>
                            </div>
                            <button type="button" onclick="openIconSelector()" class="px-3 py-1.5 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                                <i class="fas fa-search mr-1"></i>Choisir
                            </button>
                        </div>
                    </div>
                    
                    <!-- Color picker -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Couleur principale</label>
                        <div class="flex items-center space-x-3">
                            <input type="color" id="categorie-couleur" name="couleur" value="#3B82F6" 
                                   class="w-12 h-8 border border-gray-300 rounded cursor-pointer">
                            <input type="text" id="couleur-hex" value="#3B82F6" 
                                   class="flex-1 px-2 py-1.5 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                   placeholder="#3B82F6">
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">Couleur utilisée pour l'affichage</p>
                    </div>
                </div>
                
                <!-- Configuration -->
                <div class="bg-gray-50 rounded-lg p-3">
                    <h5 class="font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-cog text-green-600 mr-2"></i>Configuration
                    </h5>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ordre d'affichage</label>
                            <input type="number" class="w-full px-2 py-1.5 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" 
                                   id="categorie-ordre" name="ordre_affichage" min="0" value="0">
                            <p class="text-xs text-gray-500 mt-0.5">0 = premier, 1 = second, etc.</p>
                        </div>
                        
                        <div class="flex items-center justify-center md:justify-end">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" id="categorie-actif" name="actif" checked class="sr-only">
                                <div class="relative">
                                    <div class="block bg-gray-300 w-12 h-6 rounded-full"></div>
                                    <div class="dot absolute left-0.5 top-0.5 bg-white w-5 h-5 rounded-full transition transform"></div>
                                </div>
                                <span class="ml-2 text-sm font-medium text-gray-700">Actif</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Aperçu -->
                <div style="background: #f8fafc; border-radius: 8px; padding: 0.75rem; margin: 0.75rem 0;">
                    <h5 style="font-weight: 600; color: #1f2937; margin-bottom: 0.5rem; display: flex; align-items: center;">
                        <i class="fas fa-eye text-orange-600 mr-2"></i>Aperçu
                    </h5>
                    <div id="categorie-preview" style="background: white; border: 2px dashed #d1d5db; border-radius: 8px; padding: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div id="preview-icon" style="width: 2rem; height: 2rem; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: white; background-color: #3B82F6;">
                                <i class="fas fa-tag"></i>
                            </div>
                            <div>
                                <div id="preview-nom" style="font-weight: 600; color: #1f2937; font-size: 0.9rem;">Nom de la catégorie</div>
                                <div id="preview-description" style="font-size: 0.75rem; color: #6b7280;">Description de la catégorie</div>
                            </div>
                            <div style="margin-left: auto;">
                                <span style="padding: 0.125rem 0.375rem; background: #dcfce7; color: #166534; font-size: 0.7rem; border-radius: 9999px;">Actif</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Boutons d'action -->
                <div class="modal-footer">
                    <button type="button" onclick="closeCategoryModal()" class="btn btn-secondary">
                        <i class="fas fa-times mr-2"></i>Annuler
                    </button>
                    <button type="button" onclick="submitCategoryForm()" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Sélecteur d'icônes -->
    <div id="iconModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-60">
        <div class="bg-white rounded-lg p-6 w-full max-w-4xl mx-4 max-h-[80vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h4 class="text-xl font-semibold">
                    <i class="fas fa-icons mr-2 text-blue-600"></i>Sélectionner une Icône
                </h4>
                <button onclick="closeIconModal()" class="text-gray-500 hover:text-gray-700 text-xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Catégories d'icônes -->
            <div class="flex flex-wrap gap-2 mb-6">
                <button onclick="filterIcons('all')" class="icon-category-btn px-3 py-1 rounded-full text-sm font-medium bg-blue-600 text-white">Toutes</button>
                <button onclick="filterIcons('business')" class="icon-category-btn px-3 py-1 rounded-full text-sm font-medium bg-gray-200 text-gray-700 hover:bg-gray-300">Business</button>
                <button onclick="filterIcons('tech')" class="icon-category-btn px-3 py-1 rounded-full text-sm font-medium bg-gray-200 text-gray-700 hover:bg-gray-300">Tech</button>
                <button onclick="filterIcons('logistics')" class="icon-category-btn px-3 py-1 rounded-full text-sm font-medium bg-gray-200 text-gray-700 hover:bg-gray-300">Logistique</button>
                <button onclick="filterIcons('finance')" class="icon-category-btn px-3 py-1 rounded-full text-sm font-medium bg-gray-200 text-gray-700 hover:bg-gray-300">Finance</button>
                <button onclick="filterIcons('other')" class="icon-category-btn px-3 py-1 rounded-full text-sm font-medium bg-gray-200 text-gray-700 hover:bg-gray-300">Autres</button>
            </div>
            
            <!-- Grille d'icônes -->
            <div id="icon-grid" class="grid grid-cols-8 md:grid-cols-12 gap-3">
                <!-- Icônes générées par JavaScript -->
            </div>
        </div>
    </div>

    <style>
        /* Toggle switch personnalisé */
        #categorie-actif:checked + .relative .block {
            background-color: #10B981;
        }
        #categorie-actif:checked + .relative .dot {
            transform: translateX(24px);
        }
        #categorie-actif:checked ~ span {
            color: #10B981;
        }
        
        /* Grid d'icônes */
        .icon-item {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 1.5rem;
        }
        .icon-item:hover {
            border-color: #3B82F6;
            background: #F3F4F6;
            transform: scale(1.05);
        }
        .icon-item.selected {
            border-color: #3B82F6;
            background: #EBF8FF;
            color: #3B82F6;
        }
        
        /* Animations */
        .icon-item, .dot, .block {
            transition: all 0.3s ease;
        }
    </style>

    <!-- JavaScript -->
    <script src="js/main.js"></script>
</body>
</html>