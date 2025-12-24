/**
 * Field Operations Offline Support
 *
 * Handles offline functionality using IndexedDB and service workers
 *
 * @package CampaignPress
 * @subpackage Premium/FieldOperations
 * @since 2.0.0
 */

(function() {
    'use strict';

    var dbName = 'cp_field_ops_offline';
    var dbVersion = 1;
    var db = null;

    /**
     * Initialize IndexedDB
     */
    function initDB() {
        if (!window.indexedDB) {
            console.warn('IndexedDB not supported');
            return;
        }

        var request = indexedDB.open(dbName, dbVersion);

        request.onerror = function(event) {
            console.error('IndexedDB error:', event.target.error);
        };

        request.onsuccess = function(event) {
            db = event.target.result;
            console.log('IndexedDB initialized');
        };

        request.onupgradeneeded = function(event) {
            db = event.target.result;

            // Create object stores for offline data
            if (!db.objectStoreNames.contains('canvassing')) {
                var canvassingStore = db.createObjectStore('canvassing', { keyPath: 'id', autoIncrement: true });
                canvassingStore.createIndex('synced', 'synced', { unique: false });
                canvassingStore.createIndex('timestamp', 'timestamp', { unique: false });
            }

            if (!db.objectStoreNames.contains('phone_banking')) {
                var phoneStore = db.createObjectStore('phone_banking', { keyPath: 'id', autoIncrement: true });
                phoneStore.createIndex('synced', 'synced', { unique: false });
                phoneStore.createIndex('timestamp', 'timestamp', { unique: false });
            }

            if (!db.objectStoreNames.contains('gotv')) {
                var gotvStore = db.createObjectStore('gotv', { keyPath: 'id', autoIncrement: true });
                gotvStore.createIndex('synced', 'synced', { unique: false });
                gotvStore.createIndex('timestamp', 'timestamp', { unique: false });
            }

            if (!db.objectStoreNames.contains('walk_lists')) {
                var walkListStore = db.createObjectStore('walk_lists', { keyPath: 'id' });
                walkListStore.createIndex('last_updated', 'last_updated', { unique: false });
            }

            console.log('IndexedDB schema created');
        };
    }

    /**
     * Save data offline
     */
    function saveOffline(storeName, data) {
        if (!db) {
            console.error('Database not initialized');
            return Promise.reject('Database not available');
        }

        return new Promise(function(resolve, reject) {
            data.timestamp = Date.now();
            data.synced = false;

            var transaction = db.transaction([storeName], 'readwrite');
            var store = transaction.objectStore(storeName);
            var request = store.add(data);

            request.onsuccess = function() {
                console.log('Data saved offline:', storeName);
                resolve(request.result);
            };

            request.onerror = function() {
                console.error('Failed to save offline:', request.error);
                reject(request.error);
            };
        });
    }

    /**
     * Get unsynced data
     */
    function getUnsyncedData(storeName) {
        if (!db) {
            return Promise.resolve([]);
        }

        return new Promise(function(resolve, reject) {
            var transaction = db.transaction([storeName], 'readonly');
            var store = transaction.objectStore(storeName);
            var index = store.index('synced');
            var request = index.getAll(false);

            request.onsuccess = function() {
                resolve(request.result);
            };

            request.onerror = function() {
                reject(request.error);
            };
        });
    }

    /**
     * Mark data as synced
     */
    function markAsSynced(storeName, ids) {
        if (!db) {
            return Promise.resolve();
        }

        return new Promise(function(resolve, reject) {
            var transaction = db.transaction([storeName], 'readwrite');
            var store = transaction.objectStore(storeName);

            var promises = ids.map(function(id) {
                return new Promise(function(resolveItem, rejectItem) {
                    var getRequest = store.get(id);

                    getRequest.onsuccess = function() {
                        var data = getRequest.result;
                        if (data) {
                            data.synced = true;
                            var putRequest = store.put(data);

                            putRequest.onsuccess = function() {
                                resolveItem();
                            };

                            putRequest.onerror = function() {
                                rejectItem(putRequest.error);
                            };
                        } else {
                            resolveItem();
                        }
                    };

                    getRequest.onerror = function() {
                        rejectItem(getRequest.error);
                    };
                });
            });

            Promise.all(promises).then(resolve).catch(reject);
        });
    }

    /**
     * Sync all offline data
     */
    function syncAllData() {
        if (!navigator.onLine) {
            console.log('Cannot sync: offline');
            return Promise.resolve();
        }

        var stores = ['canvassing', 'phone_banking', 'gotv'];
        var syncPromises = stores.map(function(storeName) {
            return getUnsyncedData(storeName).then(function(data) {
                if (data.length === 0) {
                    return;
                }

                return syncStoreData(storeName, data);
            });
        });

        return Promise.all(syncPromises);
    }

    /**
     * Sync data for specific store
     */
    function syncStoreData(storeName, data) {
        // Prepare sync payload
        var syncPayload = {};
        syncPayload[storeName] = data;

        // Send to server
        return fetch(cpFieldOps.ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                action: 'cp_field_ops_sync',
                sync_data: JSON.stringify(syncPayload),
                nonce: cpFieldOps.nonce
            })
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(response) {
            if (response.success) {
                // Mark as synced
                var ids = data.map(function(item) { return item.id; });
                return markAsSynced(storeName, ids);
            } else {
                throw new Error(response.data.message || 'Sync failed');
            }
        })
        .catch(function(error) {
            console.error('Sync error:', error);
            throw error;
        });
    }

    /**
     * Cache walk list data
     */
    function cacheWalkList(walkListId, addresses) {
        if (!db) {
            return Promise.resolve();
        }

        return new Promise(function(resolve, reject) {
            var transaction = db.transaction(['walk_lists'], 'readwrite');
            var store = transaction.objectStore('walk_lists');

            var data = {
                id: walkListId,
                addresses: addresses,
                last_updated: Date.now()
            };

            var request = store.put(data);

            request.onsuccess = function() {
                console.log('Walk list cached');
                resolve();
            };

            request.onerror = function() {
                reject(request.error);
            };
        });
    }

    /**
     * Get cached walk list
     */
    function getCachedWalkList(walkListId) {
        if (!db) {
            return Promise.resolve(null);
        }

        return new Promise(function(resolve, reject) {
            var transaction = db.transaction(['walk_lists'], 'readonly');
            var store = transaction.objectStore('walk_lists');
            var request = store.get(walkListId);

            request.onsuccess = function() {
                resolve(request.result);
            };

            request.onerror = function() {
                reject(request.error);
            };
        });
    }

    // Initialize on load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDB);
    } else {
        initDB();
    }

    // Auto-sync when coming back online
    window.addEventListener('online', function() {
        console.log('Connection restored, syncing data...');
        syncAllData().then(function() {
            console.log('Sync complete');
        }).catch(function(error) {
            console.error('Sync failed:', error);
        });
    });

    // Periodic sync if configured
    if (cpFieldOps.syncInterval) {
        setInterval(function() {
            if (navigator.onLine) {
                syncAllData();
            }
        }, cpFieldOps.syncInterval);
    }

    // Expose public API
    window.CPFieldOpsOffline = {
        saveOffline: saveOffline,
        syncAllData: syncAllData,
        cacheWalkList: cacheWalkList,
        getCachedWalkList: getCachedWalkList,
        getUnsyncedData: getUnsyncedData
    };

})();
