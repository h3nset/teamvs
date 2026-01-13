/**
 * IndexedDB Manager for Padel Tournament
 * Handles offline data storage and sync queue management
 */

const DB_NAME = 'PadelTournamentDB';
const DB_VERSION = 1;

// Store names
const STORES = {
    TOURNAMENTS: 'tournaments',
    TEAMS: 'teams',
    PAIRS: 'pairs',
    MATCHES: 'matches',
    SCORES: 'scores',
    SYNC_QUEUE: 'syncQueue',
};

let db = null;

/**
 * Initialize the database
 */
export async function initDB() {
    if (db) return db;

    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, DB_VERSION);

        request.onerror = () => {
            console.error('Failed to open IndexedDB:', request.error);
            reject(request.error);
        };

        request.onsuccess = () => {
            db = request.result;
            console.log('IndexedDB initialized');
            resolve(db);
        };

        request.onupgradeneeded = (event) => {
            const database = event.target.result;

            // Tournaments store
            if (!database.objectStoreNames.contains(STORES.TOURNAMENTS)) {
                const store = database.createObjectStore(STORES.TOURNAMENTS, { keyPath: 'id' });
                store.createIndex('status', 'status', { unique: false });
            }

            // Teams store
            if (!database.objectStoreNames.contains(STORES.TEAMS)) {
                const store = database.createObjectStore(STORES.TEAMS, { keyPath: 'id' });
                store.createIndex('tournament_id', 'tournament_id', { unique: false });
            }

            // Pairs store
            if (!database.objectStoreNames.contains(STORES.PAIRS)) {
                const store = database.createObjectStore(STORES.PAIRS, { keyPath: 'id' });
                store.createIndex('team_id', 'team_id', { unique: false });
            }

            // Matches store
            if (!database.objectStoreNames.contains(STORES.MATCHES)) {
                const store = database.createObjectStore(STORES.MATCHES, { keyPath: 'id' });
                store.createIndex('tournament_id', 'tournament_id', { unique: false });
                store.createIndex('round_number', 'round_number', { unique: false });
                store.createIndex('status', 'status', { unique: false });
            }

            // Scores store
            if (!database.objectStoreNames.contains(STORES.SCORES)) {
                const store = database.createObjectStore(STORES.SCORES, { keyPath: 'id' });
                store.createIndex('match_id', 'match_id', { unique: false });
            }

            // Sync queue store
            if (!database.objectStoreNames.contains(STORES.SYNC_QUEUE)) {
                const store = database.createObjectStore(STORES.SYNC_QUEUE, { keyPath: 'id', autoIncrement: true });
                store.createIndex('status', 'status', { unique: false });
                store.createIndex('created_at', 'created_at', { unique: false });
            }
        };
    });
}

/**
 * Generic store operations
 */
async function getStore(storeName, mode = 'readonly') {
    if (!db) await initDB();
    const transaction = db.transaction(storeName, mode);
    return transaction.objectStore(storeName);
}

export async function put(storeName, data) {
    const store = await getStore(storeName, 'readwrite');
    return new Promise((resolve, reject) => {
        const request = store.put(data);
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

export async function get(storeName, id) {
    const store = await getStore(storeName);
    return new Promise((resolve, reject) => {
        const request = store.get(id);
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

export async function getAll(storeName) {
    const store = await getStore(storeName);
    return new Promise((resolve, reject) => {
        const request = store.getAll();
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

export async function getByIndex(storeName, indexName, value) {
    const store = await getStore(storeName);
    const index = store.index(indexName);
    return new Promise((resolve, reject) => {
        const request = index.getAll(value);
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

export async function remove(storeName, id) {
    const store = await getStore(storeName, 'readwrite');
    return new Promise((resolve, reject) => {
        const request = store.delete(id);
        request.onsuccess = () => resolve();
        request.onerror = () => reject(request.error);
    });
}

export async function clear(storeName) {
    const store = await getStore(storeName, 'readwrite');
    return new Promise((resolve, reject) => {
        const request = store.clear();
        request.onsuccess = () => resolve();
        request.onerror = () => reject(request.error);
    });
}

/**
 * Tournament-specific operations
 */
export async function cacheTournament(tournament) {
    await put(STORES.TOURNAMENTS, tournament);

    if (tournament.teams) {
        for (const team of tournament.teams) {
            await put(STORES.TEAMS, team);
            if (team.pairs) {
                for (const pair of team.pairs) {
                    await put(STORES.PAIRS, pair);
                }
            }
        }
    }

    if (tournament.matches) {
        for (const match of tournament.matches) {
            await put(STORES.MATCHES, match);
            if (match.scores) {
                for (const score of match.scores) {
                    await put(STORES.SCORES, score);
                }
            }
        }
    }
}

export async function getCachedTournament(id) {
    const tournament = await get(STORES.TOURNAMENTS, id);
    if (!tournament) return null;

    tournament.teams = await getByIndex(STORES.TEAMS, 'tournament_id', id);

    for (const team of tournament.teams) {
        team.pairs = await getByIndex(STORES.PAIRS, 'team_id', team.id);
    }

    tournament.matches = await getByIndex(STORES.MATCHES, 'tournament_id', id);

    for (const match of tournament.matches) {
        match.scores = await getByIndex(STORES.SCORES, 'match_id', match.id);
    }

    return tournament;
}

/**
 * Sync queue operations
 */
export async function addToSyncQueue(entityType, entityId, payload) {
    const item = {
        entity_type: entityType,
        entity_id: entityId,
        payload,
        status: 'pending',
        created_at: new Date().toISOString(),
        timestamp: new Date().toISOString(),
    };

    await put(STORES.SYNC_QUEUE, item);
    console.log('Added to sync queue:', item);

    // Request background sync if available
    if ('serviceWorker' in navigator && 'sync' in window.registration) {
        try {
            await window.registration.sync.register('sync-scores');
        } catch (e) {
            console.log('Background sync not available');
        }
    }
}

export async function getPendingSyncItems() {
    return getByIndex(STORES.SYNC_QUEUE, 'status', 'pending');
}

export async function markSynced(id) {
    const item = await get(STORES.SYNC_QUEUE, id);
    if (item) {
        item.status = 'synced';
        item.synced_at = new Date().toISOString();
        await put(STORES.SYNC_QUEUE, item);
    }
}

export async function markFailed(id) {
    const item = await get(STORES.SYNC_QUEUE, id);
    if (item) {
        item.status = 'failed';
        item.retry_count = (item.retry_count || 0) + 1;
        await put(STORES.SYNC_QUEUE, item);
    }
}

/**
 * Score saving with offline support
 */
export async function saveScoreOffline(matchId, setNumber, homeScore, awayScore, deviceId) {
    const scoreId = `${matchId}_${setNumber}`;

    const score = {
        id: scoreId,
        match_id: matchId,
        set_number: setNumber,
        home_score: homeScore,
        away_score: awayScore,
        device_id: deviceId,
        recorded_at: new Date().toISOString(),
    };

    // Save to local store
    await put(STORES.SCORES, score);

    // Add to sync queue
    await addToSyncQueue('score', scoreId, score);

    return score;
}

/**
 * Process sync queue
 */
export async function processSyncQueue(apiEndpoint) {
    const pending = await getPendingSyncItems();

    if (pending.length === 0) {
        console.log('No pending items to sync');
        return { synced: 0, failed: 0 };
    }

    console.log(`Processing ${pending.length} pending sync items`);

    const deviceId = localStorage.getItem('device_id') || 'unknown';

    try {
        const response = await fetch(apiEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                device_id: deviceId,
                items: pending.map(item => ({
                    entity_type: item.entity_type,
                    entity_id: item.entity_id,
                    payload: item.payload,
                    timestamp: item.timestamp,
                })),
            }),
        });

        if (!response.ok) {
            throw new Error(`Sync failed: ${response.status}`);
        }

        const results = await response.json();

        let synced = 0;
        let failed = 0;

        for (let i = 0; i < results.results.length; i++) {
            const result = results.results[i];
            const item = pending[i];

            if (result.status === 'synced') {
                await markSynced(item.id);
                synced++;
            } else if (result.status === 'conflict') {
                // Handle conflict - update local data with server data
                if (result.server_data) {
                    await put(STORES.SCORES, result.server_data);
                }
                await markSynced(item.id);
                synced++;
            } else {
                await markFailed(item.id);
                failed++;
            }
        }

        console.log(`Sync complete: ${synced} synced, ${failed} failed`);
        return { synced, failed };

    } catch (error) {
        console.error('Sync error:', error);

        // Mark all as failed
        for (const item of pending) {
            await markFailed(item.id);
        }

        return { synced: 0, failed: pending.length };
    }
}

// Export store names for external use
export { STORES };

// Auto-initialize on import
initDB().catch(console.error);
