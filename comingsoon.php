
<!DOCTYPE html>
<html lang="en" class="light scroll-smooth" dir="ltr">
    <head>
       <?php include 'headtag.php';?>

    </head>
    
    <body class="text-gray-900 dark:text-white dark:bg-gray-900">
        <section class="md:h-screen py-6 flex items-center justify-center relative bg-[url('../../assets/images/bg/pages.jpg')] bg-no-repeat bg-center bg-cover">
            <div class="absolute inset-0 bg-gradient-to-b from-transparent via-gray-900/70 to-gray-900 z-2"></div>
            <div class="container-fluid relative z-3">
                <div class="grid grid-cols-1">
                    <div class="flex flex-col min-h-screen justify-center text-center md:px-10 py-10 px-4">
                        <a href="index.html"><img src="codez3.png" class="mx-auto" height="120px" width="120px" alt=""></a>
                        
                        <div class="title-heading my-auto">
                            <h1 class="text-white mt-3 mb-6 md:text-6xl text-4xl font-bold">Comingsoon</h1>
                            <p class="text-white/70 text-lg max-w-xl">Codezy offers a more personalized learning experience along with the flexibility of learning at your own pace.</p>
                        
                            <div id="countdown" class="countdown-timer mt-5 pt-4" data-countdown-date="2025-12-25T12:00:01">

                                <div class="grid sm:grid-cols-4 grid-cols-2">
                                    <div class="p-2">
                                        <h1 data-days class="font-semibold text-white text-5xl">00</h1>
                                        <p class="font-medium text-white/70 mt-2">Days</p>
                                    </div>

                                    <div class="p-2">
                                        <h1 data-hours class="font-semibold text-white text-5xl">00</h1>
                                        <p class="font-medium text-white/70 mt-2">Hours</p>
                                    </div>

                                    <div class="p-2">
                                        <h1 data-minutes class="font-semibold text-white text-5xl">00</h1>
                                        <p class="font-medium text-white/70 mt-2">Minutes</p>
                                    </div>

                                    <div class="p-2">
                                        <h1 data-seconds class="font-semibold text-white text-5xl">00</h1>
                                        <p class="font-medium text-white/70 mt-2">Seconds</p>
                                    </div>
                                </div>
                            </div>
                            <div id="end-message" class="countdown-end text-xl text-white mt-5" style="display: none;">
                                The event has started!
                            </div>
                        </div>
                        
                       
                    </div>
                </div><!--end grid-->
            </div><!--end container-->
        </section><!--end section -->

        <div class="fixed bottom-3 end-3 z-10">
            <a href="" class="size-8 flex bg-primary text-white justify-center items-center rounded-lg back-button"><i class="ri-arrow-left-line"></i></a>
        </div>
        


        <!-- JAVASCRIPTS -->
        <script src="assets/js/plugins.init.js"></script>
        <script src="assets/js/app.js"></script>
        <!-- JAVASCRIPTS -->
    </body>
</html>