// ============================================================================
// FICHIER CENTRAL D'API CLIENT - ECORIDE
// ============================================================================
// Ce fichier contient la logique principale permettant à l'interface Frontend
// de communiquer avec les différents points finaux (endpoints) du Backend PHP.
// Il utilise l'API Fetch moderne de JavaScript pour des requêtes asynchrones.
//
// L'objectif principal de ce fichier est d'isoler la logique réseau pour
// éviter la redondance dans les différents contrôleurs de vues.
// ============================================================================

/**
 * Constante pointant vers la racine de l'API Backend.
 * @constant {string}
 */
const API_BASE_URL = '../../api';

// ============================================================================
// UTILITAIRES DE VALIDATION (Ajoutés pour fiabiliser les requêtes)
// ============================================================================

/**
 * Fonction utilitaire pour vérifier si une chaîne de caractères est vide.
 * Typiquement utilisé avant d'envoyer des données au serveur.
 * 
 * @param {string} str - La chaîne à tester.
 * @returns {boolean} - Vrai si la chaîne est vide ou nulle.
 */
function isEmptyString(str) {
    return (!str || str.trim().length === 0);
}

/**
 * Fonction utilitaire permettant de valider sommairement une adresse e-mail.
 * Côté client, cela évite une requête réseau inutile vers le backend PHP.
 * 
 * @param {string} email - L'adresse e-mail à vérifier.
 * @returns {boolean} - Vrai si le format de l'e-mail semble valide.
 */
function isValidEmailFormat(email) {
    if (isEmptyString(email)) return false;
    // Regex basique pour le format email d'EcoRide
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// ============================================================================
// FONCTION PRINCIPALE DE REQUÊTE
// ============================================================================

/**
 * Exécute une requête asynchrone vers l'API Backend.
 * Gère automatiquement les en-têtes JSON et les erreurs réseau ou applicatives.
 * 
 * @param {string} endpoint - Le point final (ex: 'users.php', 'trips.php').
 * @param {string} [method='GET'] - La méthode HTTP ('GET', 'POST', 'PUT', 'DELETE').
 * @param {Object|null} [body=null] - L'objet contenant les données de la requête (pour POST/PUT).
 * @returns {Promise<Object>} - Les données JSON renvoyées par l'API (généralement statut et data/message).
 * @throws {Error} - Si la réponse HTTP n'est pas OK ou si le parsing JSON échoue.
 */
async function apiCall(endpoint, method = 'GET', body = null) {
    const headers = {
        'Content-Type': 'application/json'
    };

    const options = {
        method,
        headers
    };

    if (body) {
        options.body = JSON.stringify(body);
    }

    try {
        const response = await fetch(`${API_BASE_URL}/${endpoint}`, options);
        // Ensure successful response OR parse json for error msg
        let data;
        try {
            data = await response.json();
        } catch (e) {
            throw new Error('Erreur de parsing de la réponse serveur.');
        }

        if (!response.ok) {
            throw new Error(data.message || 'Erreur API');
        }

        return data;
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}
