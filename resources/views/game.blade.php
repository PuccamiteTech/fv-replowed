<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ config('app.name', 'Laravel') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <script type="text/javascript" src="http://<?= $_SERVER['HTTP_HOST'] ?>/farmville/v855036/webassets/js/swfobject_2_2/swfobject.js"></script>

                    <script>
                        function getExperiments() {
                            console.log('getExperiments');
                            return <?= json_encode(config('experiments')) ?>;
                        }

                        function getUserInfo() {
                            console.log("getUserInfo")
                            var userInfo = {
                                "uid": "{{ auth()->user()->uid }}",
                                "name": "{{ auth()->user()->load('userMeta')->userMeta->firstName }}",
                                "pic_square": "",
                                "first_name": "{{ auth()->user()->load('userMeta')->userMeta->firstName }}",
                                "last_name": "{{ auth()->user()->userMeta->lastName }}",
                                "locale": "en_US",
                                "is_app_user": true,
                                "allowed_restrictions": false,
                                "pic_big": ""
                            };
                            return userInfo;
                        }

                        function closeOnLoadPopDialogs() {
                            console.log("closeOnLoadPopDialogs")
                        }

                        function getFriendData() {
                            console.log("getFriendData")
                            var friendData = {!! json_encode($neighbors ?? []) !!};
                            return friendData;
                        }

                        function getAppFriendIds() {
                            console.log("getAppFriendIds")
                            var appFriendIds = {!! json_encode($neighborIds ?? []) !!};
                            return appFriendIds;
                        }

                        function addNeighborById(neighborId) {
                            fetch('/neighbors/add', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ neighbor_id: neighborId })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    console.log('Neighbor added successfully');
                                    // Reload page or update dynamically
                                    location.reload();
                                }
                            })
                            .catch(error => console.error('Error:', error));
                        }

                        function removeNeighborById(neighborId) {
                            fetch('/neighbors/remove', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ neighbor_id: neighborId })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    console.log('Neighbor removed successfully');
                                    location.reload();
                                }
                            })
                            .catch(error => console.error('Error:', error));
                        }

                        function getNonAppFriendsInfo() {
                            console.log("getNonAppFriendsInfo")
                            tResult = {
                                "requestedFriends": {
                                    "Facebook": null
                                }
                            };
                            jsResult = {
                                "data": [{
                                    "uid": "20000",
                                    "first_name": "getNonAppFriendsInfo",
                                    "last_name": "getNonAppFriendsInfo",
                                    "name": "getNonAppFriendsInfo",
                                    "picture": {
                                        "data": {
                                            "url": ""
                                        }
                                    },
                                    "valid": true,
                                    "is_app_user": false,
                                    "allowed_restrictions": false,
                                    "pic_big": ""
                                }]
                            };
                            document.getElementById('flashapp').onNonAppFriendsCallback(jsResult);
                            // document.getElementById('flashapp').onNonAppFriendsCallback(tResult, jsResult);
                        }

                        function getNonAppFriendsInfoV2() {
                            console.log("getNonAppFriendsInfoV2")
                            response = [{
                                "uid": "30000",
                                "first_name": "getNonAppFriendsInfoV2",
                                "last_name": "getNonAppFriendsInfoV2",
                                "name": "getNonAppFriendsInfoV2",
                                "pic_square": "",
                                "is_app_user": true,
                                "allowed_restrictions": false,
                                "pic_big": ""
                            }];
                            document.getElementById('flashapp').onNonAppFriendsV2Callback(response);
                        }

                        function checkForPublishPermission(uid) {
                            console.log("checkForPublishPermission", uid)
                            hasPublishPermission = 1;
                            hasEmailPermission = 1;
                            document.getElementById('flashapp').onCheckForPublishPermissionComplete(hasPublishPermission, hasEmailPermission);
                        }

                        function safeConsoleLog(message) {
                            console.log("safeConsoleLog", message)
                        }

                        function onLoadStep(step) {
                            console.log("onLoadStep", step)
                            return ""
                        }

                        function onPostInit() {
                            console.log("onPostInit")
                        }

                        function onWorldLoad() {
                            console.log("onWorldLoad")
                            return false
                        }

                        function initZoom() {
                            console.log("initZoom")
                        }

                        function viewItemXmlInArtTool(param1, param2) {
                            console.log("viewItemXmlInArtTool: ", param2, param1)
                        }

                        function ztrackCount(counter, kingdom, phylum, zclass) {
                            console.log("ztrackCount: counter " + counter + " | kingdom " + kingdom + " | plylum " + phylum + " | zclass " + zclass)
                            return ""
                        }

                        function getFlashMovie(movieName) {
                            return null;
                        }

                        function getPreloaderScreenshot(swf, param1) {
                            console.log("getPreloaderScreenshot")
                        }

                        function getWorld() {
                            return null;
                        }

                        function getCurrentWorldType() {
                            var currentWorldType = "farm";
                            return currentWorldType;
                        }

                        let allPotentialNeighbors = [];
                        let currentActiveTab = 'pending';

                        // Open modal
                        function openNeighborModal() {
                            document.getElementById('neighborModal').style.display = 'block';
                            loadPendingRequests();
                            switchTab('pending');
                        }

                        // Close modal
                        function closeNeighborModal() {
                            document.getElementById('neighborModal').style.display = 'none';
                        }

                        // Close modal when clicking outside
                        window.onclick = function(event) {
                            const modal = document.getElementById('neighborModal');
                            if (event.target == modal) {
                                closeNeighborModal();
                            }
                        }

                        // Switch tabs
                        function switchTab(tabName) {
                            currentActiveTab = tabName;
                            
                            // Hide all contents
                            document.querySelectorAll('.tab-content').forEach(content => {
                                content.style.display = 'none';
                            });
                            
                            // Reset tab styles
                            document.querySelectorAll('.neighbor-tab').forEach(tab => {
                                tab.style.backgroundColor = '#B8D4E3';
                                tab.style.color = '#333';
                            });
                            
                            // Show selected content
                            document.getElementById(tabName + 'Content').style.display = 'block';
                            document.getElementById(tabName + 'Tab').style.backgroundColor = '#7FB3D5';
                            document.getElementById(tabName + 'Tab').style.color = 'white';
                            
                            // Load data according to tab
                            if (tabName === 'pending') {
                                loadPendingRequests();
                            } else if (tabName === 'current') {
                                loadCurrentNeighbors();
                            } else if (tabName === 'find') {
                                loadPotentialNeighbors();
                            }
                        }

                        // Load pending requests
                        function loadPendingRequests() {
                            fetch('/neighbors/pending')
                                .then(response => response.json())
                                .then(data => {
                                    const pendingList = document.getElementById('pendingList');
                                    const pendingCount = document.getElementById('pendingCount');
                                    
                                    pendingCount.textContent = data.count;
                                    
                                    if (data.pending.length === 0) {
                                        pendingList.innerHTML = '<p style="text-align: center; color: #7F8C8D; padding: 20px; font-style: italic;">📭 No pending requests</p>';
                                    } else {
                                        pendingList.innerHTML = data.pending.map(neighbor => {
                                            const initial = neighbor.first_name.charAt(0).toUpperCase();
                                            return `
                                                <div class="neighbor-item">
                                                    <div class="neighbor-info">
                                                        <div class="neighbor-avatar">${initial}</div>
                                                        <div>
                                                            <div class="neighbor-name">${neighbor.first_name} ${neighbor.last_name}</div>
                                                            <div class="neighbor-id">ID: ${neighbor.uid}</div>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <button class="btn-action btn-accept" onclick="acceptNeighbor('${neighbor.uid}')">✓ Accept</button>
                                                        <button class="btn-action btn-reject" onclick="rejectNeighbor('${neighbor.uid}')">✗ Reject</button>
                                                    </div>
                                                </div>
                                            `;
                                        }).join('');
                                    }
                                })
                                .catch(error => {
                                    console.error('Error loading neighbor requests:', error);
                                    document.getElementById('pendingList').innerHTML = '<p style="text-align: center; color: #E74C3C;">❌ Error loading neighbor requests</p>';
                                });
                        }

                        // Load current neighbors
                        function loadCurrentNeighbors() {
                            fetch('/neighbors/data')
                                .then(response => response.json())
                                .then(data => {
                                    const currentList = document.getElementById('currentList');
                                    const currentCount = document.getElementById('currentCount');
                                    const neighbors = data.neighbors || [];
                                    
                                    currentCount.textContent = neighbors.length;
                                    
                                    if (neighbors.length === 0) {
                                        currentList.innerHTML = '<p style="text-align: center; color: #7F8C8D; padding: 20px; font-style: italic;">👥 You don\'t have neighbors yet</p>';
                                    } else {
                                        currentList.innerHTML = neighbors.map(neighbor => {
                                            const initial = neighbor.first_name.charAt(0).toUpperCase();
                                            return `
                                                <div class="neighbor-item">
                                                    <div class="neighbor-info">
                                                        <div class="neighbor-avatar">${initial}</div>
                                                        <div>
                                                            <div class="neighbor-name">${neighbor.first_name} ${neighbor.last_name}</div>
                                                            <div class="neighbor-id">ID: ${neighbor.uid}</div>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <button class="btn-action btn-remove" onclick="removeNeighbor('${neighbor.uid}')">🗑️ Remove</button>
                                                    </div>
                                                </div>
                                            `;
                                        }).join('');
                                    }
                                })
                                .catch(error => {
                                    console.error('Error loading neighbors:', error);
                                    document.getElementById('currentList').innerHTML = '<p style="text-align: center; color: #E74C3C;">❌ Error loading neighbors</p>';
                                });
                        }

                        // Load available users
                        function loadPotentialNeighbors() {
                            fetch('/neighbors/potential')
                                .then(response => response.json())
                                .then(data => {
                                    allPotentialNeighbors = data.users || [];
                                    displayPotentialNeighbors(allPotentialNeighbors);
                                })
                                .catch(error => {
                                    console.error('Error loading users:', error);
                                    document.getElementById('findList').innerHTML = '<p style="text-align: center; color: #E74C3C;">❌ Error loading users</p>';
                                });
                        }

                        // Display available users
                        function displayPotentialNeighbors(users) {
                            const findList = document.getElementById('findList');
                            
                            if (users.length === 0) {
                                findList.innerHTML = '<p style="text-align: center; color: #7F8C8D; padding: 20px; font-style: italic;">🔍 No users found</p>';
                            } else {
                                findList.innerHTML = users.map(user => {
                                    const initial = user.first_name.charAt(0).toUpperCase();
                                    return `
                                        <div class="neighbor-item">
                                            <div class="neighbor-info">
                                                <div class="neighbor-avatar">${initial}</div>
                                                <div>
                                                    <div class="neighbor-name">${user.first_name} ${user.last_name}</div>
                                                    <div class="neighbor-id">ID: ${user.uid}</div>
                                                </div>
                                            </div>
                                            <div>
                                                <button class="btn-action btn-add" onclick="sendNeighborRequest('${user.uid}')">➕ Add</button>
                                            </div>
                                        </div>
                                    `;
                                }).join('');
                            }
                        }

                        // Filter available users
                        function filterPotentialNeighbors() {
                            const searchTerm = document.getElementById('searchNeighbor').value.toLowerCase();
                            const filtered = allPotentialNeighbors.filter(user => {
                                return user.first_name.toLowerCase().includes(searchTerm) ||
                                    user.last_name.toLowerCase().includes(searchTerm) ||
                                    user.name.toLowerCase().includes(searchTerm) ||
                                    user.uid.toLowerCase().includes(searchTerm);
                            });
                            displayPotentialNeighbors(filtered);
                        }

                        // Accept neighbor
                        function acceptNeighbor(neighborId) {
                            if (!confirm('Do you want to accept this neighbor request?')) return;
                            
                            fetch('/neighbors/accept', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ neighbor_id: neighborId })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert('✅ ' + data.message);
                                    loadPendingRequests();
                                    // Reload page to update game
                                    setTimeout(() => location.reload(), 1500);
                                } else {
                                    alert('❌ Error accepting neighbor');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('❌ Error processing neighbor request');
                            });
                        }

                        // Reject neighbor
                        function rejectNeighbor(neighborId) {
                            if (!confirm('Do you want to reject this neighbor request?')) return;
                            
                            fetch('/neighbors/reject', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ neighbor_id: neighborId })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert('✅ ' + data.message);
                                    loadPendingRequests();
                                } else {
                                    alert('❌ Error rejecting neighbor request');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('❌ Error processing neighbor request');
                            });
                        }

                        // Remove neighbor
                        function removeNeighbor(neighborId) {
                            if (!confirm('Do you want to remove this neighbor?')) return;
                            
                            fetch('/neighbors/remove', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ neighbor_id: neighborId })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert('✅ ' + data.message);
                                    loadCurrentNeighbors();
                                    // Reload page to update game
                                    setTimeout(() => location.reload(), 1500);
                                } else {
                                    alert('❌ Error removing neighbor');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('❌ Error processing neighbor request');
                            });
                        }

                        // Send request
                        function sendNeighborRequest(neighborId) {
                            if (!confirm('Do you want to send this neighbor request?')) return;
                            
                            fetch('/neighbors/send-request', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ neighbor_id: neighborId })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert('✅ ' + data.message);
                                } else {
                                    alert('❌ ' + (data.error || 'Error sending neighbor request'));
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('❌ Error sending neighbor request');
                            });
                        }

                        // Load request counter on page load
                        document.addEventListener('DOMContentLoaded', function() {
                            fetch('/neighbors/pending')
                                .then(response => response.json())
                                .then(data => {
                                    if (data.count > 0) {
                                        // Show badge on Add Neighbors button
                                        const addNeighborBtn = document.querySelector('a[title="Add Neighbors"]');
                                        if (addNeighborBtn) {
                                            addNeighborBtn.innerHTML += `<span style="position: absolute; top: 5px; right: 5px; background-color: #E74C3C; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold;" id="notificationBadge">${data.count}</span>`;
                                            addNeighborBtn.style.position = 'relative';
                                        }
                                    }
                                })
                                .catch(error => console.error('Error loading counter:', error));
                        });

                        // Update notification badge
                        function updateNotificationBadge() {
                            fetch('/neighbors/pending')
                                .then(response => response.json())
                                .then(data => {
                                    const badge = document.getElementById('notificationBadge');
                                    if (badge) {
                                        badge.textContent = data.count;
                                        if (data.count > 0) {
                                            badge.style.backgroundColor = '#E74C3C';
                                        }
                                    }
                                })
                                .catch(error => console.error('Error updating badge:', error));
                        }

                        // Update every 30 seconds
                        setInterval(updateNotificationBadge, 30000);

                        // Update on load
                        document.addEventListener('DOMContentLoaded', updateNotificationBadge);
                    </script>

                    <script>
                        // FarmNS module

                        if (typeof(FarmNS) === "undefined") {
                            FarmNS = {}
                        }

                        FarmNS.getFlash = function() {
                            return document.getElementById("flashapp")
                        };

                        FarmNS.setZid = function(zid) {
                            console.log("FarmNS.setZid:", zid)
                        };

                        FarmNS.initW2e = function() {
                            console.log("FarmNS.initW2e")
                        };

                        FarmNS.showW2eIron = function() {
                            console.log("FarmNS.showW2eIron")
                        };

                        FarmNS.FlashExtendedPermissionsManager = {
                            getPermissions: function() {
                                console.log("FarmNS.FlashExtendedPermissionsManager.getPermissions")
                                return [];
                            },
                            refreshExtendedPermsFlash: function(callId) {
                                console.log("FarmNS.FlashExtendedPermissionsManager.refreshExtendedPermsFlash")
                                FarmNS.getFlash().doRegisteredCallback(callId, [{
                                    publish_actions: true,
                                    user_games_activity: true,
                                    friends_games_activity: true,
                                    publish_actions: true,
                                    user_birthday: true,
                                    read_stream: true,
                                    user_friends: true,
                                    extended_permissions_gift_given: true
                                }])
                            },
                            requestExtendedPermsFlash: function(callId, perms_list, e) {
                                console.log("FarmNS.FlashExtendedPermissionsManager.requestExtendedPermsFlash:", perms_list, e)
                                FarmNS.getFlash().doRegisteredCallback(callId, [{}])
                            },
                            requestExtendedPerms: function(callId, f, e) {
                                console.log("FarmNS.FlashExtendedPermissionsManager.requestExtendedPerms:", callId, f, e)
                            },
                            checkFriendsPermissionFlash: function() {
                                console.log("FarmNS.FlashExtendedPermissionsManager.checkFriendsPermissionFlash")
                                FarmNS.getFlash().onFriendsDataLoaded(true)
                            }
                        }

                        FarmNS.Request2Manager = {
                            shareFarmstamaticPhoto: function(imagePath, message) {
                                console.log("FarmNS.Request2Manager.shareFarmstamaticPhoto:", imagePath, message)
                            },
                            sendRequestsFromFlash: function(requestData, requestMessage, requestTitle, uids, statsSource) {
                                console.log("FarmNS.Request2Manager.sendRequestsFromFlash: ", requestData, requestMessage, requestTitle, uids, statsSource)
                                let res = {
                                    'request_ids': uids
                                }
                                FarmNS.getFlash().fbresponseHandler(res)
                            }
                        }

                        FarmNS.UISandboxManager = {
                            setSendAssetNameCallbackID: function(callId) {
                                console.log("FarmNS.UISandboxManager.setSendAssetNameCallbackID")
                            },
                            setRemoveAssetNameCallbackID: function(callId2) {
                                console.log("FarmNS.UISandboxManager.setRemoveAssetNameCallbackID")
                            },
                            setAssetToSynced: function(assetName) {
                                console.log("FarmNS.UISandboxManager.setAssetToSynced")
                            },
                            onFlashLoadComplete: function() {
                                console.log("FarmNS.UISandboxManager.onFlashLoadComplete")
                            }
                        }
                    </script>

                    <script>
                        // FB module

                        if (typeof(FB) === "undefined") {
                            FB = {}
                        }

                        FB.Facebook = {}
                        FB.Facebook.apiClient = {
                            callMethod: function(method, params, callback) {
                                console.log("FB.Facebook.apiClient.callMethod")
                                console.log(" - method:", method)
                                console.log(" - params:", params)
                                console.log(" - callback:", callback)

                                if (method === "friends.getAppUsers") {
                                    console.log(params["auth_token"])
                                    result = ["10000"]
                                    callback([], null)
                                    //callback(result, null)
                                } else if (method === "friends.get") {
                                    var result = [

                                    ];
                                    callback([], null)
                                    //callback(result, null)
                                } else if (method === "users.getLoggedInUser") { // This is not working properly. 
                                    var result = {
                                        uid: "{{ auth()->user()->uid }}",
                                        firstName: "{{ auth()->user()->load('userMeta')->userMeta->firstName }}",
                                        name: "{{ auth()->user()->userMeta->lastName }}",
                                        picture: "",
                                    }
                                    res = callback(result, null)
                                    console.log(" - result", res)
                                    return result
                                } else if (method === "users.getInfo") {
                                    var result = [

                                    ];
                                    callback(result, null)
                                } else if (method === "users.hasAppPermission") {
                                    callback(true, null)
                                }
                            }
                        }
                    </script>
                    <script>
                        var flashVars = {
                            "token": "2f0daceecd5afb8e59c89777513e844e92",
                            "master_id": "{{ auth()->user()->uid }}",
                            "serverTime": <?= time() ?>,
                            "app_url": "http://<?= $_SERVER['HTTP_HOST'] ?>/farmville/",
                            "sn_app_url": "http://<?= $_SERVER['HTTP_HOST'] ?>/farmville/",
                            "asset_url": "http://<?= $_SERVER['HTTP_HOST'] ?>/farmville/assets/hashed/",
                            "isCIP": false,
                            "CHROME_FLASH_FIX_1131_CLONE": true,
                            // "items_amf_pristine_version": "http://<?= $_SERVER['HTTP_HOST'] ?>/farmville/assets/hashed/itemsamfpristine/v2/",
                            "TRANSACTION_LATENCY_POPULATION": 1,
                            "TRANSACTION_LATENCY_MAX_ID": 100,
                            "TIMED_ACTION_MILLISECONDS_OPS": 5,
                            "AMF_DROPPED_CONNECTION_MAX_RETRIES": 10,
                            // "OPS_SHOULD_IGNORE_NETWORK_CHANGE_TEMPRT": false,
                            "flashRevision": "855037.855026",
                            "phpRevision": "855038",
                            "configRevision": "",
                            "xml_url": "http://<?= $_SERVER['HTTP_HOST'] ?>/farmville/xml/gz/v855038/",
                            "master_assethash_url": "http://<?= $_SERVER['HTTP_HOST'] ?>/farmville/assethash/v9/",
                            "masterysigns_amf_url": "http://<?= $_SERVER['HTTP_HOST'] ?>/farmville/masterysigns/v1/",
                            "ITEMS_AMF_BUILD_TIME_REDUCTION": false,
                            "swfLocation": "http://<?= $_SERVER['HTTP_HOST'] ?>/farmville/embeds/Flash/v855037.855026/FarmGame.855037.855026.swf",
                            "parts_count": 3,
                            "NO_FUEL_DAY_START_TIME": "1606723200",
                            "NO_FUEL_DAY_END_TIME": "1607328000",
                            "NO_FUEL_DAY_WORLDS": "yuletide",
                            "OPS_JS_GET_FRIENDS_PERMISSION": false,
                            "game_config_url": "http://<?= $_SERVER['HTTP_HOST'] ?>/farmville/xml/gz/v855038/gameSettings.xml.gz",
                            "gameSettingsCMS_url": "http://<?= $_SERVER['HTTP_HOST'] ?>/farmville/xml/gz/v855038/gameSettingsCMS.xml.gz",
                            "items_url": "http://<?= $_SERVER['HTTP_HOST'] ?>/farmville/xml/gz/v855038/items.xml.gz",
                            "IS_MASTERY_CLEANED": true,
                            "fgsm_amf_url": "http://<?= $_SERVER['HTTP_HOST'] ?>/farmville/xml/gz/v855038/fgsm.amf.gz",
                            "FGSM_AMF_ENABLED": false,
                            "OPS_FGSM_QUEST_ITEM_CAT_ENABLED": true,
                            "OPS_SOCIAL_PLUMBING_CLEANUP_TMPRT": 0,
                            "OPS_SOCIAL_PLUMBING_CLEANUP_LOGGING_TMPRT": true,
                            "OPS_TEMPID_ON_PLOTS_TMPRT": true,
                            "R2_NEIGHBOR_AUTOPOP_ENABLE": false,
                            "dialogs_url": "http://<?= $_SERVER['HTTP_HOST'] ?>/farmville/xml/gz/v855038/dialogs.xml.gz",
                            "quest_url": "http://<?= $_SERVER['HTTP_HOST'] ?>/farmville/xml/gz/v855038/questSettings.xml.gz",
                            "quest_min_url": "http://<?= $_SERVER['HTTP_HOST'] ?>/farmville/xml/gz/v855038/questSettings_0.xml.gz",
                            "OPS_TRACK_MEMORY_TRENDING": true,
                            "OPS_MEMORY_TRACKING_TIMEINTERVAL_MINUTES": 2,
                            "OPS_FLASH_CRASH_TRACKING_SECONDS": 4000,
                            "FEATURE_IFRAME": 1,
                            "FARM_SLOTS_MIN_SPIN_DELAY_MS": 1000,
                            "MEMORY_CLEANUP_LOCAL_DATA_GC": true,
                            "fotd": "http://<?= $_SERVER['HTTP_HOST'] ?>/farmville/assets/hashed/assets/fotd/Current/5169f96f29c9856ac53111433cdfff63.jpg",
                            "fotdChangeTime": 10,
                            "locale": "en_US",
                            "fblocale": "en_US",
                            "regiftFeedDailyCount": 0,
                            "FEATURE_MERGEDITEMFLAG_OPT_IN_ENABLED": true,
                            "dbg_tool_mode": 0,
                            "ui_sandbox_mode": 1,
                            "force_enable_toggle_admin": 0,
                            "artupload_tool_mode": 0,
                            "quest_tool2_mode": 0,
                            "CiproToProd": "false",
                            "fb_sig_session_key": "",
                            "fb_sig_expires": "0",
                            "fb_sig_user": "{{ auth()->user()->uid }}",
                            "oauth_session": true,
                            "fb_sig_api_key": "80c6ec6628efd9a465dd223190a65bbc",
                            "fb_sig_app_id": "102452128776",
                            "fb_sig_base_domain": "farmville.com",
                            "fb_sig_ss": "102452128776",
                            "fb_sig_time": <?= time() ?>,
                            "isAdult": "0",
                            "sequence_id": 1606900539,
                            "waterEnabled": 1,
                            "showAd": 0,
                            "showInterstitialAd": 0,
                            "canFertilize": 1,
                            "giftIcon": 1,
                            "giftIdleMission": 1,
                            "giftMission": 1,
                            "authBypass": 1,
                            // "batch_limit_experiment": 1,
                            "iframeRedirect": 1,
                            "soundOptimizedCheck": "false",
                            "rasterFrameJump": 3,
                            "animationMemoryLimit": 150,
                            "zaspSampleRate": 1,
                            "bridgeAgent": "false",
                            "zaspSession": "false",
                            "zaspFPS": "false",
                            "zaspWait": "false",
                            "zaspWF": "false",
                            "zaspGPI": "false",
                            "fv_dev_terrain_mapping_creation_tool": 0,
                            "disallowWither": 0,
                            "disallowPetRunaway": 0,
                            "featureExtraMastery": 0,
                            "featureExtraMasteryAnimal": 0,
                            "featureExtraMasteryTree": 0,
                            "featureExtraMasteryBloom": 0,
                            "featureExtraMasteryCropMultiplier": 2,
                            "featureExtraMasteryAnimalMultiplier": 2,
                            "featureExtraMasteryTreeMultiplier": 2,
                            "featureExtraMasteryBloomMultiplier": 1,
                            "FEATURE_ENABLE_SWEET_SEEDS_FOR_HAITI": 1,
                            "FEATURE_FLASH_CAN_ASK_EMAIL": 0,
                            "FEATURE_FLASHPARAM_GIFTBOX_EXPANSION": 1,
                            "GIFTBOX_TOTAL_ITEM_LIMIT": 10000,
                            "FEATURE_SILO_CAPACITY_LEVEL_CAP": 36,
                            "FEATURE_ORCHARDS_LEVEL_CAP": 3,
                            "FEATURE_ANIMAL_PEN_LEVEL_CAP": 6,
                            "FEATURE_ANIMAL_PEN_EXPANSIONS": 1,
                            "FEATURE_MARKET_STALL_CAPACITY_LEVEL_CAP": 139,
                            "FEATURE_FLASHPARAM_REPORT_ERRORS": 1,
                            "FEATURE_FLASHPARAM_REPORT_SWF_EXPORT_ERRORS": 0,
                            "batch_limit_runtime": 1,
                            "FEATURE_TRAVEL_ANIMATION_ASSET": "assets/dialogs/yuletide/7d2f72f99b34e0390c342e587d16c69b.swf",
                            "FLASHVAR_CRASHBUSTERS_LOGSIZE": 20,
                            "FLASHVAR_TRANSACTION_MAX_WAIT": 5000,
                            "IsInDomainShardingV2": 1,
                            "FLASHVAR_T6_LOAD_STATS_SAMPLE": 1000,
                            "FLASHVAR_LAB_SAMPLE": 100,
                            "FLASHVAR_FARM_66598_SAMPLE": 10000,
                            "FLASHVAR_FARM_65294_SAMPLE": 10000,
                            "FLASHVAR_ANIMAL_FEED_THROTTLE": 82800,
                            "QUEST_FEED_STATS_SAMPLE": 100,
                            "PHP_FEED_STATS_SAMPLE": 100,
                            "FEEDS_AS_LINK_STATS_SAMPLE": 1,
                            "FEEDS_V2_STATS_SAMPLE": 1,
                            "STREAMPUBLISH_USE_PHP_SDK": true,
                            "SHOW_FEED_POSTING_CONFIRMATION_DIALOG": true,
                            "FEATURE_FLASHPARAM_HUD_ICON_BLACKLIST": "xmoStarter,GypsyTrader,flower_shack,dragon_pen_finished,bobsberry,mystree_v2,community_matchmaking_v3,bingoXPromo,mystree,BushelBabiesV1Building,HolidayGivingTree,HlightsStarter,autumnorchard2013,ItemMembership,hangingGardens,hangingGardensFTUE,mayflowergarden2013,scratchCard,lottery,GlenStarter,JadeFallsStarter,fcSlotMachine,leSlotMachine,stpatricksbuildable2013Building,gardenamphitheater2013Building,ferriswheel2012Building,pearup,puzzleFeature,multiPanelFeatureSelector,gnomevinyard2013Building,windmill2012Building,bigbarnyard2012Building,bumpercar2012Building,carnivalBooth,GildasList,completionPack,CandyStarter,lemonaidStand,lemonaidStandv2,dreamdeer,roadtrip2013,FforestStarter,irrigation_placeSprinkler,irrigation_placeWell_16hr,irrigation_placeWell_8hr,xtiStarter,MatchmakingBeta,xdwStarter,stencil",
                            "FLASHGIFTQUEUEDICON_CONDITIONALLYADDFLASHGIFTICON_OVERRIDE": false,
                            "MAX_TRANSACTION_DEPTH": 50,
                            "FEATURE_FLASHPARAM_MYSTERY_CRATE_APPLY_LOAD_CHECK": true,
                            "FEATURE_FLASHPARAM_PEN_CONTENTS_SAMPLE": 0,
                            "PERF_MAX_BATCH_FRIENDSETS": 20,
                            "TIMED_ACTION_MAX_RETRIES": 1000,
                            "SAMPLE_OVERRIDE_FUEL": 10000,
                            "SAMPLE_OVERRIDE_FARM_WORLD_ACTION": 1000,
                            "SAMPLE_OVERRIDE_ERRORS": 100,
                            "batchLimitFunctionExceptions": "%7B%0A%20%20%20%20%22UserService.saveFeatureOptions%22%3A%201%2C%0A%20%20%20%20%22UserService.publishUserAction%22%3A%201%2C%0A%20%20%20%20%22LeaderboardService.getPassedFriendFeed%22%3A%201%2C%0A%20%20%20%20%22WorldService.performAction%22%3A%201%2C%0A%20%20%20%20%22FarmService.saveIcons%22%3A%201%2C%0A%20%20%20%20%22AvatarService.saveAvatar%22%3A%201%0A%7D",
                            "batchLimiterVerboseData": 1,
                            "req_FlashControllerStartTimestamp": <?= time() ?>,
                            "debugMode": true,
                            "neighbors": "{{ $neighborsBase64 ?? '' }}"
                        };

                        var swfCallback = function(e) {

                        }
                        var params = {
                            allowScriptAccess: "always",
                            wmode: "default",
                            allowFullScreen: "true"
                        };
                        var attrs = {
                            id: "flashapp",
                            name: "flashapp"
                        };
                        swfobject.embedSWF("http://<?= $_SERVER['HTTP_HOST'] ?>/farmville/embeds/Flash/v855037.855026/FV_Preloader.swf", "flashContent",
                            "100%", "600", "10.0.0", "playerProductInstall.swf",
                            flashVars, params, attrs, swfCallback);
                    </script>

                    <center>
                        <!-- Neighbor Management Modal -->
                        <div id="neighborModal" style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6);">
                            <div style="background-color: #fefefe; margin: 5% auto; padding: 0; border: 3px solid #8B4513; width: 600px; border-radius: 10px; box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2), 0 6px 20px 0 rgba(0,0,0,0.19); font-family: Arial, sans-serif;">
                                <!-- Header -->
                                <div style="padding: 15px 20px; background: linear-gradient(to bottom, #7FB3D5 0%, #5C9FCC 100%); color: white; border-radius: 7px 7px 0 0; display: flex; justify-content: space-between; align-items: center;">
                                    <h2 style="margin: 0; font-size: 20px; text-shadow: 1px 1px 2px rgba(0,0,0,0.3);">🌾 Manage Neighbors</h2>
                                    <span onclick="closeNeighborModal()" style="cursor: pointer; font-size: 28px; font-weight: bold; color: white; text-shadow: 1px 1px 2px rgba(0,0,0,0.3);">&times;</span>
                                </div>
                                
                                <!-- Tabs -->
                                <div style="display: flex; background-color: #E8F4F8; border-bottom: 2px solid #5C9FCC;">
                                    <button class="neighbor-tab" onclick="switchTab('pending')" id="pendingTab" style="flex: 1; padding: 12px; background-color: #7FB3D5; color: white; border: none; cursor: pointer; font-size: 14px; font-weight: bold; transition: background-color 0.3s;">
                                        Requests <span id="pendingCount" style="background-color: #E74C3C; border-radius: 50%; padding: 2px 8px; font-size: 12px; margin-left: 5px;">0</span>
                                    </button>
                                    <button class="neighbor-tab" onclick="switchTab('current')" id="currentTab" style="flex: 1; padding: 12px; background-color: #B8D4E3; color: #333; border: none; cursor: pointer; font-size: 14px; font-weight: bold; transition: background-color 0.3s;">
                                        My Neighbors <span id="currentCount" style="background-color: #3498DB; color: white; border-radius: 50%; padding: 2px 8px; font-size: 12px; margin-left: 5px;">0</span>
                                    </button>
                                    <button class="neighbor-tab" onclick="switchTab('find')" id="findTab" style="flex: 1; padding: 12px; background-color: #B8D4E3; color: #333; border: none; cursor: pointer; font-size: 14px; font-weight: bold; transition: background-color 0.3s;">
                                        Add Neighbors
                                    </button>
                                </div>
                                
                                <!-- Content -->
                                <div style="padding: 20px; max-height: 400px; overflow-y: auto; background-color: #FFF9E6;">
                                    <!-- Pending Requests Tab -->
                                    <div id="pendingContent" class="tab-content">
                                        <div id="pendingList" style="display: flex; flex-direction: column; gap: 10px;">
                                            <p style="text-align: center; color: #7F8C8D; font-style: italic;">Loading requests...</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Current Neighbors Tab -->
                                    <div id="currentContent" class="tab-content" style="display: none;">
                                        <div id="currentList" style="display: flex; flex-direction: column; gap: 10px;">
                                            <p style="text-align: center; color: #7F8C8D; font-style: italic;">Loading neighbors...</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Find Neighbors Tab -->
                                    <div id="findContent" class="tab-content" style="display: none;">
                                        <div style="margin-bottom: 15px;">
                                            <input type="text" id="searchNeighbor" placeholder="Search by name or ID..." style="width: 100%; padding: 10px; border: 2px solid #7FB3D5; border-radius: 5px; font-size: 14px; box-sizing: border-box;" onkeyup="filterPotentialNeighbors()">
                                        </div>
                                        <div id="findList" style="display: flex; flex-direction: column; gap: 10px;">
                                            <p style="text-align: center; color: #7F8C8D; font-style: italic;">Loading users...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <style>
                            .neighbor-item {
                                display: flex;
                                align-items: center;
                                justify-content: space-between;
                                padding: 12px 15px;
                                background: white;
                                border: 2px solid #D5E8F0;
                                border-radius: 8px;
                                transition: all 0.3s;
                            }
                            
                            .neighbor-item:hover {
                                border-color: #7FB3D5;
                                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                                transform: translateY(-2px);
                            }
                            
                            .neighbor-info {
                                display: flex;
                                align-items: center;
                                gap: 12px;
                            }
                            
                            .neighbor-avatar {
                                width: 45px;
                                height: 45px;
                                border-radius: 50%;
                                background: linear-gradient(135deg, #7FB3D5 0%, #5C9FCC 100%);
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                color: white;
                                font-weight: bold;
                                font-size: 18px;
                                border: 2px solid #5C9FCC;
                            }
                            
                            .neighbor-name {
                                font-weight: bold;
                                color: #2C3E50;
                                font-size: 15px;
                            }
                            
                            .neighbor-id {
                                font-size: 12px;
                                color: #7F8C8D;
                            }
                            
                            .btn-action {
                                padding: 8px 16px;
                                border: none;
                                border-radius: 5px;
                                cursor: pointer;
                                font-size: 13px;
                                font-weight: bold;
                                transition: all 0.3s;
                                margin-left: 5px;
                            }
                            
                            .btn-accept {
                                background-color: #27AE60;
                                color: white;
                            }
                            
                            .btn-accept:hover {
                                background-color: #229954;
                                transform: scale(1.05);
                            }
                            
                            .btn-reject {
                                background-color: #E74C3C;
                                color: white;
                            }
                            
                            .btn-reject:hover {
                                background-color: #C0392B;
                                transform: scale(1.05);
                            }
                            
                            .btn-remove {
                                background-color: #E67E22;
                                color: white;
                            }
                            
                            .btn-remove:hover {
                                background-color: #D35400;
                                transform: scale(1.05);
                            }
                            
                            .btn-add {
                                background-color: #3498DB;
                                color: white;
                            }
                            
                            .btn-add:hover {
                                background-color: #2980B9;
                                transform: scale(1.05);
                            }
                            
                            .btn-action:disabled {
                                background-color: #BDC3C7;
                                cursor: not-allowed;
                                transform: none;
                            }
                            
                            #neighborModal::-webkit-scrollbar,
                            .tab-content::-webkit-scrollbar {
                                width: 8px;
                            }
                            
                            #neighborModal::-webkit-scrollbar-track,
                            .tab-content::-webkit-scrollbar-track {
                                background: #F0F0F0;
                                border-radius: 10px;
                            }
                            
                            #neighborModal::-webkit-scrollbar-thumb,
                            .tab-content::-webkit-scrollbar-thumb {
                                background: #7FB3D5;
                                border-radius: 10px;
                            }
                            
                            #neighborModal::-webkit-scrollbar-thumb:hover,
                            .tab-content::-webkit-scrollbar-thumb:hover {
                                background: #5C9FCC;
                            }

                            /* Tooltip */
                            .tooltip {
                                position: relative;
                                display: inline-block;
                            }

                            .tooltip .tooltiptext {
                                visibility: hidden;
                                width: 200px;
                                background-color: #2C3E50;
                                color: #fff;
                                text-align: center;
                                border-radius: 6px;
                                padding: 8px;
                                position: absolute;
                                z-index: 1;
                                bottom: 125%;
                                left: 50%;
                                margin-left: -100px;
                                opacity: 0;
                                transition: opacity 0.3s;
                                font-size: 12px;
                            }

                            .tooltip .tooltiptext::after {
                                content: "";
                                position: absolute;
                                top: 100%;
                                left: 50%;
                                margin-left: -5px;
                                border-width: 5px;
                                border-style: solid;
                                border-color: #2C3E50 transparent transparent transparent;
                            }

                            .tooltip:hover .tooltiptext {
                                visibility: visible;
                                opacity: 1;
                            }
                        </style>

                        <img src="img/logo.png" style="width: 250px;" />
                        <div>
                            <!-- SESSION HEADER -->
                            <div id="header" style="width: 850px; margin-top: 50px;">
                                <div id="session" style="float: right; font-size: 14px;">
                                    <p style="display:inline">Welcome <b>{{ auth()->user()->load('userMeta')->userMeta->firstName }}</b>! {{ auth()->user()->load('userMeta')->userMeta->xp }} xp (UID: {{ auth()->user()->uid }})</p>
                                </div>
                            </div>
                            <br>
                            <!-- GAME BAR -->
                            <div style="overflow-x:hidden;overflow-y:hidden;width:1018px;height:40px;background-image:url(/img/game_bar/ecb8d4257f9af29b38f1a10c4ccb322c4ebb2e8c.png);background-color: transparent; background-position: 324px 20px; background-repeat: no-repeat; margin: 0px; padding: 0px; ">
                                <div style="position:relative;float:left;height:40px;background-image:url(/img/game_bar/ecb8d4257f9af29b38f1a10c4ccb322c4ebb2e8c.png);background-color: transparent; background-position: 0px 20px; background-repeat: no-repeat; border-width: 0px; border-style: none; margin: 0px; padding: 0px 0px 0px 5px; border-color: white; ">
                                    <a href="#" title="Free Gifts" style="color:rgb(59, 89, 152);cursor:pointer;width:91px;background-image:url(/img/game_bar/c7ae80613dbb3a1aa848ddf6fb3baea29c233ec6.png);background-color: transparent; text-decoration:none;float:left;height:27px;margin: 11px 0px 0px 4px; " target="_blank"></a>
                                    <a href="#" title="Play" style="color:rgb(59, 89, 152);cursor:pointer;width:36px;background-image:url(/img/game_bar/8bba081e4b32e771144af4a7404209007dff7756.png);background-color: transparent; text-decoration:none;float:left;height:27px;background-position: 0px -27px; margin: 11px 0px 0px 4px;" target="_blank"></a>
                                    <a href="#" title="Add Neighbors" onclick="openNeighborModal(); return false;" id="addNeighborsBtn" style="color:rgb(59, 89, 152);cursor:pointer;width:99px;background-image:url(/img/game_bar/c82d6a5be3328edc136a4f5f2ba7f3ed6228f7b4.png);background-color: transparent; text-decoration:none;float:left;height:27px;margin: 11px 0px 0px 4px; position: relative;"></a>
                                    <!--a href="#" title="Add Neighbors" onclick="document.getElementById('flashapp').popR2AddNeighbor('open')" style="color:rgb(59, 89, 152);cursor:pointer;width:99px;background-image:url(/img/game_bar/c82d6a5be3328edc136a4f5f2ba7f3ed6228f7b4.png);background-color: transparent; text-decoration:none;float:left;height:27px;margin: 11px 0px 0px 4px; "></a-->
                                    <a href="#" target="_blank" title="Add Farm Coins &amp; Cash" style="color:rgb(59, 89, 152);cursor:pointer;width:278px;background-image:url(/img/game_bar/15509e6078e7b7da47c0cd1bf5c643bd9b2ddb64.png);background-color: transparent; text-decoration:none;float:left;height:27px;margin: 11px 0px 0px 4px; ">
                                        <div style="position:absolute;width:160px;height:36px;background-image:url(/img/game_bar/a8e4cf2a4842f3dec98ec268d66475c8173b8026.png);background-color: transparent; left:362px;top:1px;display:block;background-repeat: no-repeat; margin: 0px; padding: 0px; ">
                                            <!-- Timer -->
                                            <div style="position:absolute;top:17px;width:11px;height:16px;text-align:center;font-size:13px;font-weight:bold;left:67px;margin: 0px; padding: 0px; ">0</div>
                                            <div style="position:absolute;top:17px;width:11px;height:16px;text-align:center;font-size:13px;font-weight:bold;left:80px;margin: 0px; padding: 0px; ">0</div>
                                            <div style="position:absolute;top:17px;width:11px;height:16px;text-align:center;font-size:13px;font-weight:bold;left:99px;margin: 0px; padding: 0px; ">0</div>
                                            <div style="position:absolute;top:17px;width:11px;height:16px;text-align:center;font-size:13px;font-weight:bold;left:112px;margin: 0px; padding: 0px; ">0</div>
                                            <div style="position:absolute;top:17px;width:11px;height:16px;text-align:center;font-size:13px;font-weight:bold;left:131px;margin: 0px; padding: 0px; ">0</div>
                                            <div style="position:absolute;top:17px;width:11px;height:16px;text-align:center;font-size:13px;font-weight:bold;left:144px;margin: 0px; padding: 0px; ">0</div>
                                        </div>
                                    </a>
                                    <a href="#" target="_blank" title="Redeem" style="color:rgb(59, 89, 152);cursor:pointer;width:59px;background-image:url(/img/game_bar/fb98c926d5cb2a763cf3695fc9d774330224fca6.png);background-color: transparent; text-decoration:none;float:left;height:27px;margin: 11px 0px 0px 4px; "></a>
                                    <a href="#" title="Add Farm Coins &amp; Cash" style="color:rgb(59, 89, 152);cursor:pointer;width:106px;background-image:url(/img/game_bar/db55c5639428cd3f20f517053c60229fa4f1d78f.png);background-color: transparent; display:none;text-decoration:none;float:left;height:27px;margin: 11px 0px 0px 4px; " target="_blank"></a>
                                    <a href="#" target="_blank" title="Help" style="color:rgb(59, 89, 152);cursor:pointer;width:45px;background-image:url(/img/game_bar/b1bb2f3b3104f295aa5349b78a77f8eaf5313d7c.png);background-color: transparent; text-decoration:none;float:left;height:27px;margin: 11px 0px 0px 4px; "></a>
                                </div>
                                <div style="float:right;margin: 0px; padding: 0px; ">
                                    <div style="float:left;width:0px;margin: 0px; padding: 15px 5px 0px 0px; ">
                                        <a href="#" style="color:rgb(59, 89, 152);cursor:pointer;text-decoration:none;" target="_blank">
                                            <img src="/img/game_bar/2d9375ecade3323f674d30aa2060f304ff36456a.png" style="width:0px;border-width: 0px; border-style: none; border-color: white; ">
                                        </a>
                                    </div>
                                    <div style="cursor:pointer;height:35px;position:relative;float:right;width:26px;display:block;margin: -2px 5px 0px 0px; padding: 0px; ">
                                        <div style="background-image:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABoAAAAcCAYAAAB/E6/TAAADG0lEQVRIx7WW2S/jURTH+0/Yd2pLJP4DGSQ88+BZ4g+YFzx79MCDRLzMi4cRmQnpvndUR2u6UdReSy1BrCU0DJ3v3HNMG0tXTJOPqv5yPvece+65JJI0Xm63+7Narf4jl8thtVq9kv/xmp2dbSOJ0+mEy+WCUqmExWLxf7hEo9Gw5Pj4mFleXoZCocDExMT7ZJ7q6h5BQIA0kAs+pSxxVVXJBUgH93Pak0p+VVS0CxClsjImjsQEk4ps5eVyAV5iT4ZY1AsSl9AqlU7+lErxFqaek0TU1BSylJYiLmVlUSYTYOvq6okrmZ6e9puam2EuKYnJjzTQDww8iPNW+EoyMzMjo0Nob2mBobgYxndgIgYHodfrQ+LsFUUlXq/3C0m2t7fhbGuDrrAwZfRFRTHxjY9DHHKWicwKJGaz+UGMEj7lhKqhAeqCgrhoUkTZ1/cYT6WiUfVbQgOS/rCwsIDb21tMtbZCkZfHKPPz34xfZLO0tAQxHzm+hH7Q3BIpwmg0wtHbC1lublrIxaKeom1shMVk4sFrt9s5KxadnZ3h6uoKNDTpS+vICBS1tRjLyYnJeALM/f1QiRg6nQ6rq6vY29vjrKKicDiM+/t77O/v80NqsQpTdze+ZWXh+1Oys2OiqK+H8d+e0DUSCARYEld0d3eH6+treDyex2tAJsNYTQ1GMzPjYhwa4mepqdbX16OCpCJqipubG+zs7EB0JWen7+zE14yMKCMCdUcHdKJMFEMckVeClEShUIgzo+/EYea9o9KM1dVhVNw9puFhFogr/VmZ3iyiJrm8vMTu7i7dprDZbHA6HFwmat9EgrRFp6en2NjYgM/nY0iwuLjIbG1tvV8UDAZ5n6hNKSD9r3BycsLvh4eHPLJIRmL6PW0RSajNV1ZWWEAZXVxc8HMR0dHREQ4ODjiQ3+/H3NwcC2lhKYkoCJWHSnV+fh7do3giWlAk4NraGncfVYD2NKaIykRngAT0+WUzpCIiSECVmJ+f53j0mUVarZZXsrm5yRnQ/sTqulRFEWjPIk1DM1RC48ZgMPCZoEn+0fA4Exn9BW/8y0YOrTiKAAAAAElFTkSuQmCC);bottom:1px;height:30px;overflow-x:hidden;overflow-y:hidden;position:absolute;width:26px;background-repeat: no-repeat; margin: 0px; padding: 0px; ">
                                            <!-- Notifications -->
                                            <div style="color:white;font-size:11px;font-weight:normal;left:3px;position:absolute;right:4px;text-align:center;top:4px;width:20px;display:block;margin: 0px; padding: 0px; ">0</div>
                                        </div>
                                    </div>
                                </div>
                                <div style="clear:both;margin: 0px; padding: 0px; "></div>
                            </div>
                            <div id="innerFlashDiv" style="width: 1018px; height: 600px;">
                                <div id="flashContent" style="width: 100%; height: 600px;"></div>
                            </div>
                            <!-- PREFETCHED ASSETS -->
                            <link rel="prefetch" href="http://<?= $_SERVER['HTTP_HOST'] ?>/farmville/xml/gz/v855038/items_opt.amf">
                            <link rel="prefetch" href="http://<?= $_SERVER['HTTP_HOST'] ?>/farmville/xml/gz/v855038/gameSettings.xml.gz">
                            <link rel="prefetch" href="http://<?= $_SERVER['HTTP_HOST'] ?>/farmville/xml/gz/v855038/gameSettingsCMS.xml.gz">
                            <link rel="prefetch" href="http://<?= $_SERVER['HTTP_HOST'] ?>/farmville/xml/gz/v855038/FarmConfig.swf">
                            <link rel="prefetch" href="http://<?= $_SERVER['HTTP_HOST'] ?>/farmville/xml/gz/v855038/en_US.swf">
                        </div>
                        <div style="margin-top: 50px;">
                            <p style="font-size: 11px;"></p>
                        </div>
                    </center>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>