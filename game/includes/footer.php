</div> <!-- Close game-container -->

<script src="../js/game-api.js"></script>
<script src="../js/main.js"></script>
<?php if (isset($extraJs))
    echo $extraJs; ?>

<script>
    // Global preloader handling
    window.addEventListener('load', () => {
        setTimeout(() => {
            const preloader = document.getElementById('preloader');
            if (preloader) preloader.classList.add('hidden');
        }, 500);
    });
</script>
</body>

</html>