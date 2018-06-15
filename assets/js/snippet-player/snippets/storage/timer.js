define(function() {

	// Timer class responisble for save and reading the playlist start time from local storage
	
	var skipThreshold = 6;
	var timerThrehold = 300;
	var loggerEnabled = 0;

	function logger(message, object) {
		if (loggerEnabled) {
			console.info(message, object);
		}
	};
	//getters
	function getLocalStorageObject() {
		logger('getLocalStorageObject', JSON.parse(localStorage.getItem('playlisterMDS')));
		return JSON.parse(localStorage.getItem('playlisterMDS'));
	};

	function getCurrentSkips(playlistId) {

		var localStorageObject = getLocalStorageObject();
		logger('getCurrentSkips', localStorageObject[playlistId].playCount);
		return localStorageObject[playlistId].playCount;
	};

	function getCurrentStartTime(playlistId) {
		var localStorageObject = getLocalStorageObject();
		logger('getCurrentStartTime', localStorageObject[playlistId].startDate);
		return localStorageObject[playlistId].startDate;
	};

	//checking
	function checkIfInLocalStorage(playlistId) {
		var localStorageObject = getLocalStorageObject();
		var playlistFound = false;
		if ( typeof (localStorageObject[playlistId]) != 'undefined' ) {
			playlistFound = true;
		}
		logger('checkIfInLocalStorage', playlistFound);
		return playlistFound;
	};

	

	///saving/updating
	function updateCurrentDateInLocalStorage(playlistId) {
		var localStorageObject = getLocalStorageObject();
		currentDate = new Date();
		if ( typeof(localStorageObject[playlistId] == 'undefined') || typeof(localStorageObject[playlistId].startDate == 'undefined') ) {
			localStorageObject[playlistId] = {
				startDate: currentDate,
				playCount : 0
			};
		} else {
			localStorageObject[playlistId].startDate = currentDate;
			localStorageObject[playlistId].playCount = 0;
		}
		
		logger('updateCurrentDateInLocalStorage', localStorageObject);
		saveObjectToLocalStorage(localStorageObject);
	};

	function updatePlayCountInLocalStorage(playlistId, count) {
		var localStorageObject = getLocalStorageObject();
		localStorageObject[playlistId].playCount = count;
		logger('updatePlayCountInLocalStorage', count);
		saveObjectToLocalStorage(localStorageObject);
	};

	function saveObjectToLocalStorage(objectToSave) {
		logger('saveObjectToLocalStorage', JSON.stringify(objectToSave));
		localStorage.setItem('playlisterMDS', JSON.stringify(objectToSave));
	};



	return {

		canSkip: function(playlistId) {

			if ( getLocalStorageObject()  != null ) {

				if (checkIfInLocalStorage(playlistId)) {
					//found playlsit in localstorage
					var currentSkips = getCurrentSkips(playlistId);
					if (currentSkips == skipThreshold) {
						var currentDate = new Date();
						var currentDateNowMinusOneHour = currentDate.setHours(currentDate.getHours() - 1);
						var storedPlaylistDate = new Date(getCurrentStartTime(playlistId));
						if (storedPlaylistDate < currentDateNowMinusOneHour) {
							// so update date, skip count and return true
							updateCurrentDateInLocalStorage(playlistId);
							updatePlayCountInLocalStorage(playlistId, 1);
							return true;
						} else {
							//cannot skip so return false
							return false;
						}
					} else if (currentSkips < skipThreshold) {
						updatePlayCountInLocalStorage(playlistId, currentSkips + 1);
						return true;
					}
					
				} else {
					//didnt find playlist in localstorage
					updateCurrentDateInLocalStorage(playlistId);
					updatePlayCountInLocalStorage(playlistId, 1);
					return true;
				}


			} else {
				//the localstorage has never been set ducky
				saveObjectToLocalStorage({});
				updateCurrentDateInLocalStorage(playlistId);
				updatePlayCountInLocalStorage(playlistId, 1);
				return true;
			}

			
		},

		getCount: function(playlistId) {
			if ( getLocalStorageObject()  != null ) {
				if (checkIfInLocalStorage(playlistId)) {
					return getCurrentSkips(playlistId);
				} else {
					return 0;
				}
			} else {
				return 0;
			}
		},

		getSkipLimit: function() {
			return skipThreshold;
		},

		checkIfShouldReset: function(playlistId) {
			if ( getLocalStorageObject()  != null ) {

				if (checkIfInLocalStorage(playlistId)) {
					var currentDate = new Date();
					var currentDate2 = new Date();
					var currentDateNowMinusOneHour = new Date(currentDate2.setHours(currentDate2.getHours() - 1));
					var storedPlaylistDate = new Date(getCurrentStartTime(playlistId));
						if (storedPlaylistDate < currentDateNowMinusOneHour) {
							// so update date, skip count and return true
							updateCurrentDateInLocalStorage(playlistId);
							logger('resettedStorePlaylistSkips', null);
							return true;
						}
				}
			}
		}

	};

});
