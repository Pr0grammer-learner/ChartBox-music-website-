let audioPlayer;

document.addEventListener('DOMContentLoaded', function () {
    audioPlayer = document.getElementById('audio-player');
    let playPauseButton = document.getElementById('play-pause-button');
    let playButton = document.getElementById('icon-button-play');
    let volumeButton = document.getElementById('volume-button');
    let volumeControl = document.getElementById('volume-control');
    let progressContainer = document.getElementById('progress-container');
    let progressBar = document.getElementById('progress-bar');
    let timeDisplay = document.getElementById('time-left');
    let trackDuration = document.getElementById('time-right');
    let currentIndex = 0;
    let tracks = [];
    let isDragging = false;
    let activeButton = null;

    let prevButton = document.getElementById('prev-icon'); // Исправил на prev-icon
    let nextButton = document.getElementById('next-icon'); // Исправил на next-icon

    let trackElements = document.querySelectorAll('.track-card'); // Исправил на track-card
    trackElements.forEach(function (trackElement, index) { // Добавил параметр index
        let title = trackElement.getAttribute('data-title');
        let artist = trackElement.getAttribute('data-artist');
        let image = trackElement.getAttribute('data-image');
        let audio = trackElement.getAttribute('data-audio');

        tracks.push({
            title: title,
            artist: artist,
            image: image,
            audio: audio,
            id: index // Добавил id трека равным индексу
        });

        console.log(tracks);
    });

    window.playTrack = function (button, trackCardId) {
        let trackCard = document.getElementById(trackCardId);
        let trackIndex = parseInt(trackCard.getAttribute('data-index'));
    
        let audioSrc = button.getAttribute('data-audio');
        let absolutePath = new URL(audioSrc, window.location.href).href;
    
        if (audioPlayer.src && audioPlayer.src !== absolutePath) {
            if (activeButton) {
                activeButton.classList.remove('active');
            }
        }
    
        if (audioPlayer.src === absolutePath) {
            togglePlayPause();
        } else {
            audioPlayer.src = absolutePath;
            playAudio();
            button.classList.add('active');
            activeButton = button;
    
            let trackTitleElement = document.getElementById('track-title');
            let trackArtistElement = document.getElementById('track-artist');
            let trackImageElement = document.getElementById('track-image');
    
            let trackTitle = trackCard.getAttribute('data-title');
            let trackArtist = trackCard.getAttribute('data-artist');
            let trackImage = trackCard.getAttribute('data-image');
    
            let maxTitleLength = 20;
            let displayedTitle = trackTitle.length > maxTitleLength
                ? trackTitle.substring(0, maxTitleLength) + '...' : trackTitle;
    
            trackTitleElement.textContent = displayedTitle;
            trackArtistElement.textContent = trackArtist;
            trackImageElement.src = trackImage;
    
            currentIndex = trackIndex;
        }
    };
    
    window.playPrevTrack = function () {
        let prevTrack = getPrevTrack();
        playTrackByIndex(prevTrack);
    };
    
    window.playNextTrack = function () {
        let nextTrack = getNextTrack();
        playTrackByIndex(nextTrack);
    };
    
    function playTrackByIndex(index) {
        let trackCard = document.querySelector('[data-index="' + index + '"]');
        if (trackCard) {
            let trackCardId = trackCard.id;
            playTrack(trackCard.querySelector('.play-button'), trackCardId);
        } else {
            console.error('Элемент карточки трека не найден для index:', index);
        }
    }
    
    progressContainer.addEventListener('mousedown', function () {
        isDragging = true;
    });
    
    document.addEventListener('mouseup', function () {
        isDragging = false;
    });
    
    progressContainer.addEventListener('mousemove', function (event) {
        if (isDragging) {
            let rect = progressContainer.getBoundingClientRect();
            let relativeX = event.clientX - rect.left;
            let progressPercentage = (relativeX / rect.width) * 100;
    
            progressBar.style.setProperty('--progress', progressPercentage + '%');
            updateCirclePosition();
            updateTimeDisplay();
        }
    });
    
    progressBar.addEventListener('mousemove', function (event) {
        updateHoverTime(event);
    });
    
    progressBar.addEventListener('mouseleave', function () {
        hideHoverTime();
    });
    
    progressBar.addEventListener('click', function (event) {
        seekTo(event);
    });
    
    function getNextTrack() {
        currentIndex = (currentIndex + 1) % tracks.length;
        return currentIndex;
    }
    
    function getPrevTrack() {
        currentIndex = (currentIndex - 1 + tracks.length) % tracks.length;
        return currentIndex;
    }
    
    function updateCirclePosition() {
        var circle = progressBar.querySelector('::before');
        if (circle) {
            circle.style.left = 'calc(min(100%, max(0%, var(--progress)))) - 8px';
        }
    }
    
    function updateHoverTime(event) {
        let progress = (event.offsetX / progressBar.clientWidth);
        let hoverTime = progress * audioPlayer.duration;
    
        let timeLeft = formatTime(hoverTime);
    
        document.getElementById('time-left').textContent = timeLeft;
    
        progressBar.style.setProperty('--progress', progress);
    }
    
    function hideHoverTime() {
        timeDisplay.textContent = '';
        trackDuration.textContent = '';
        progressBar.style.setProperty('--progress', (audioPlayer.currentTime / audioPlayer.duration) * 100 + '%');
    }
    
    function seekTo(event) {
        let progress = (event.offsetX / progressBar.clientWidth);
        audioPlayer.currentTime = progress * audioPlayer.duration;
    }
    
    function togglePlayPause() {
        if (audioPlayer.paused) {
            playAudio();
            playButton.classList.add('playing'); // Add the 'playing' class
        } else {
            pauseAudio();
            playButton.classList.remove('playing'); // Remove the 'playing' class
        }
    }
    
    function playAudio() {
        let playerContainer = document.getElementById('player-container');
    
        if (playerContainer) {
            playerContainer.style.visibility = 'visible';
        } else {
            console.error("Элемент 'player-container' не найден.");
        }
    
        audioPlayer.play();
        updatePlayPauseIcon();
    }
    
    function pauseAudio() {
        audioPlayer.pause();
        updatePlayPauseIcon();
    }
    
    function updatePlayPauseIcon() {
        let playPauseIcon = playPauseButton;
        if (audioPlayer.paused) {
            playPauseIcon.src = 'icons/play.png';
        } else {
            playPauseIcon.src = 'icons/pause.png';
        }
    }
    
    audioPlayer.addEventListener('timeupdate', function () {
        updateProgressBar();
        updateTimeDisplay();
    });
    
    audioPlayer.addEventListener('ended', function () {
        var playerContainer = document.getElementById('player-container');
        if (activeButton) {
            activeButton.classList.remove('active');
        }
        playerContainer.style.visibility = 'hidden';
    });
    
    audioPlayer.addEventListener('loadedmetadata', function () {
        trackDuration.textContent = formatTime(audioPlayer.duration);
    });
    
    playPauseButton.addEventListener('click', function () {
        togglePlayPause();
    });
    
    volumeControl.addEventListener('input', function () {
        changeVolume(volumeControl.value, audioPlayer);
    });
    
    progressContainer.addEventListener('mousemove', function (event) {
        let rect = progressContainer.getBoundingClientRect();
        let relativeX = event.clientX - rect.left;
        let progressPercentage = (relativeX / rect.width) * 100;
    
        progressBar.style.setProperty('--progress', progressPercentage + '%');
        updateCirclePosition();
    });
    
    function updateCirclePosition() {
        let circle = progressBar.querySelector('::before');
        if (circle) {
            circle.style.left = 'calc(min(100%, max(0%, var(--progress)))) - 8px';
        }
    }
    
    function updateTimeDisplay() {
        timeDisplay.textContent = formatTime(audioPlayer.currentTime);
    }
    
    function updateProgressBar() {
        var progress = (audioPlayer.currentTime / audioPlayer.duration) * 100;
    
        if (isFinite(progress)) {
            progressBar.value = progress;
        }
    }
    
    function updateTimeDisplay() {
        timeDisplay.textContent = formatTime(audioPlayer.currentTime);
        trackDuration.textContent = formatTime(audioPlayer.duration);
    }
    
    function changeVolume(value, audioPlayer) {
        audioPlayer.volume = parseFloat(value);
        console.log('Текущая громкость:', audioPlayer.volume.toFixed(1));
    }
    
    volumeButton.addEventListener('click', function () {
        toggleMute();
        updateVolumeIcon();
    });
    
    function toggleMute() {
        if (audioPlayer.muted) {
            audioPlayer.muted = false;
            volumeControl.value = audioPlayer.volume;
        } else {
            audioPlayer.muted = true;
            volumeControl.value = 0;
        }
        updateVolumeIcon();
    }
    
    function updateVolumeIcon() {
        var volumeIcon = document.getElementById('volume-icon');
    
        if (audioPlayer.muted || audioPlayer.volume === 0) {
            volumeIcon.src = 'icons/volume-off.png';
        } else {
            volumeIcon.src = 'icons/volume.png';
        }
    }
    
    document.addEventListener('DOMContentLoaded', function () {
        updateVolumeIcon();
    });
    
    function formatTime(seconds) {
        let minutes = Math.floor(seconds / 60);
        let remainingSeconds = Math.floor(seconds % 60);
        return (
            (minutes < 10 ? '0' : '') + minutes + ':' +
            (remainingSeconds < 10 ? '0' : '') + remainingSeconds
        );
    }
    });