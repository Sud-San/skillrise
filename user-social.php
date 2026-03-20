
<!DOCTYPE html>
<html lang="en" class="light scroll-smooth" dir="ltr">
    <head>
       <?php include 'headtag.php';?>

    </head>
    
    <body class="text-gray-900 dark:text-white dark:bg-gray-900">
        <?php include 'user-header.php';?>

        <!-- Start Hero -->
        <section class="relative bg-gray-50 dark:bg-gray-800 py-24 border border-gray-100 dark:border-gray-700">
            <div class="container relative">
                <div class="p-6 bg-white dark:bg-gray-900 rounded-lg shadow-sm shadow-gray-100 dark:shadow-gray-800">
                    <div class="grid md:grid-cols-12 items-center gap-6">
                        <div class="lg:col-span-4 md:col-span-6">
                            <div class="md:flex md:items-center md:text-start text-center">
                                <div class="profile-pic">
                                    <input id="pro-img" name="profile-image" type="file" class="hidden" onchange="loadFile(event)" />
                                    <div class="relative h-28 w-28 mx-auto">
                                        <img src="assets/images/team/1.jpg" class="rounded-full shadow-sm shadow-gray-100 dark:shadow-gray-800 ring-4 ring-gray-50 dark:ring-gray-800" id="profile-image" alt="">
                                        <label class="absolute inset-0 cursor-pointer" for="pro-img"></label>
                                    </div>
                                </div>
        
                                <div class="md:mt-0 md:ms-4 mt-4">
                                    <h5 class="text-lg font-semibold">Calvin Carlo</h5>
                                    <p class="text-gray-400">calvin@hotmail.com</p>
                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-8 md:col-span-6 md:text-end text-center">
                            <div class="grid lg:grid-cols-12 grid-cols-1 items-center gap-6">
                                <div class="lg:col-span-7">
                                    <div class="grid grid-cols-3 mx-auto gap-4">
                                        <div class="counter-box text-center">
                                            <h1 class="text-lg font-semibold mb-1"><span class="counter-value" data-target="457">123</span>+</h1>
                                            <h5 class="counter-head text-gray-400 text-sm font-medium">Projects</h5>
                                        </div>
                                        
                                        <div class="counter-box text-center">
                                            <h1 class="text-lg font-semibold mb-1">$<span class="counter-value" data-target="4667">1246</span></h1>
                                            <h5 class="counter-head text-gray-400 text-sm font-medium">Earnings</h5>
                                        </div>
                                        
                                        <div class="counter-box text-center">
                                            <h1 class="text-lg font-semibold mb-1"><span class="counter-value" data-target="93">0</span>%</h1>
                                            <h5 class="counter-head text-gray-400 text-sm font-medium">Success Rate</h5>
                                        </div>
                                    </div>
                                </div>
        
                                <div class="lg:col-span-5">
                                    <a href="" class="h-9 px-4 tracking-wider inline-flex items-center justify-center text-sm font-medium rounded-lg bg-primary text-white me-1">Follow</a>
                                    <a href="" class="h-9 px-4 tracking-wider inline-flex items-center justify-center text-sm font-medium rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-white">Hire Me</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!--end container-->

            <div class="container relative mt-6">
                <div class="grid md:grid-cols-12 grid-cols-1 gap-6">
                    <div class="lg:col-span-4 md:col-span-4">
                        <div class="p-6 rounded-lg shadow-sm shadow-gray-100 dark:shadow-gray-800 bg-white dark:bg-gray-900">
                            <h5 class="font-semibold">Personal Details :</h5>
                            <p class="text-gray-400 text-sm mt-2">I have started my career as a trainee and prove my self and achieve all the milestone with good guidance and reach up to the project manager. In this journey, I understand all the procedure which make me a good developer, team leader, and a project manager.</p>

                            <div class="mt-4">
                                <div class="flex items-center">
                                    <i class="ri-mail-line text-primary text-xl me-2.5"></i>
                                    <div class="flex-1">
                                        <h6 class="font-semibold text-sm mb-0">Email :</h6>
                                        <a href="" class="text-gray-400 text-sm">calvin@hotmail.com</a>
                                    </div>
                                </div>
                                <div class="flex items-center mt-3">
                                    <i class="ri-bookmark-line text-primary text-xl me-2.5"></i>
                                    <div class="flex-1">
                                        <h6 class="font-semibold text-sm mb-0">Skills :</h6>
                                        <a href="" class="text-gray-400 text-sm">html</a>, <a href="" class="text-gray-400 text-sm">css</a>, <a href="" class="text-gray-400 text-sm">js</a>, <a href="" class="text-gray-400 text-sm">mysql</a>
                                    </div>
                                </div>
                                <div class="flex items-center mt-3">
                                    <i class="ri-italic text-primary text-xl me-2.5"></i>
                                    <div class="flex-1">
                                        <h6 class="font-semibold text-sm mb-0">Language :</h6>
                                        <a href="" class="text-gray-400 text-sm">English</a>, <a href="" class="text-gray-400 text-sm">Japanese</a>, <a href="" class="text-gray-400 text-sm">Chinese</a>
                                    </div>
                                </div>
                                <div class="flex items-center mt-3">
                                    <i class="ri-globe-line text-primary text-xl me-2.5"></i>
                                    <div class="flex-1">
                                        <h6 class="font-semibold text-sm mb-0">Website :</h6>
                                        <a href="" class="text-gray-400 text-sm">www.calvincarlo.com</a>
                                    </div>
                                </div>
                                <div class="flex items-center mt-3">
                                    <i class="ri-gift-line text-primary text-xl me-2.5"></i>
                                    <div class="flex-1">
                                        <h6 class="font-semibold text-sm mb-0">Birthday :</h6>
                                        <p class="text-gray-400 text-sm mb-0">2nd March, 1996</p>
                                    </div>
                                </div>
                                <div class="flex items-center mt-3">
                                    <i class="ri-map-pin-line text-primary text-xl me-2.5"></i>
                                    <div class="flex-1">
                                        <h6 class="font-semibold text-sm mb-0">Location :</h6>
                                        <a href="" class="text-gray-400 text-sm">Beijing, China</a>
                                    </div>
                                </div>
                                <div class="flex items-center mt-3">
                                    <i class="ri-phone-line text-primary text-xl me-2.5"></i>
                                    <div class="flex-1">
                                        <h6 class="font-semibold text-sm mb-0">Cell No :</h6>
                                        <a href="" class="text-gray-400 text-sm">(+12) 1254-56-4896</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!--end col-->

                    <div class="lg:col-span-8 md:col-span-8">
                        <div class="rounded-lg shadow-sm shadow-gray-100 dark:shadow-gray-800 bg-white dark:bg-gray-900">
                            <div class="p-6 border-b border-gray-100 dark:border-gray-800">
                                <h5 class="font-semibold">Social Profiles :</h5>
                            </div>

                            <div class="p-6">
                                <div class="md:flex">
                                    <div class="md:w-1/3">
                                        <span class="font-medium">Twitter</span>
                                    </div>

                                    <div class="md:w-2/3 mt-4 md:mt-0">
                                        <form>
                                            <div class="form-icon relative">
                                                <i class="ri-twitter-x-line absolute top-2 start-3"></i>
                                                <input type="text" class="form-input ps-10 pe-2 py-2 h-10 text-sm rounded-lg w-full bg-transparent border border-gray-100 dark:border-gray-800 focus:shadow-none focus:ring-0 placeholder:text-gray-400 outline-none" placeholder="X Profile Name" id="twitter_name" name="name">
                                            </div>
                                        </form>

                                        <p class="text-gray-400 text-sm mt-1">Add your X username (e.g. jesus).</p>
                                    </div>
                                </div>
                                
                                <div class="md:flex mt-8">
                                    <div class="md:w-1/3">
                                        <span class="font-medium">Facebook</span>
                                    </div>

                                    <div class="md:w-2/3 mt-4 md:mt-0">
                                        <form>
                                            <div class="form-icon relative">
                                                <i class="ri-facebook-line absolute top-2 start-3"></i>
                                                <input type="text" class="form-input ps-10 pe-2 py-2 h-10 text-sm rounded-lg w-full bg-transparent border border-gray-100 dark:border-gray-800 focus:shadow-none focus:ring-0 placeholder:text-gray-400 outline-none" placeholder="Facebook Profile Name" id="facebook_name" name="name">
                                            </div>
                                        </form>

                                        <p class="text-gray-400 text-sm mt-1">Add your Facebook username (e.g. jesus).</p>
                                    </div>
                                </div>
                                
                                <div class="md:flex mt-8">
                                    <div class="md:w-1/3">
                                        <span class="font-medium">Instagram</span>
                                    </div>

                                    <div class="md:w-2/3 mt-4 md:mt-0">
                                        <form>
                                            <div class="form-icon relative">
                                                <i class="ri-instagram-line absolute top-2 start-3"></i>
                                                <input type="text" class="form-input ps-10 pe-2 py-2 h-10 text-sm rounded-lg w-full bg-transparent border border-gray-100 dark:border-gray-800 focus:shadow-none focus:ring-0 placeholder:text-gray-400 outline-none" placeholder="Instagram Profile Name" id="insta_name" name="name">
                                            </div>
                                        </form>

                                        <p class="text-gray-400 text-sm mt-1">Add your Instagram username (e.g. jesus).</p>
                                    </div>
                                </div>
                                
                                <div class="md:flex mt-8">
                                    <div class="md:w-1/3">
                                        <span class="font-medium">Linkedin</span>
                                    </div>

                                    <div class="md:w-2/3 mt-4 md:mt-0">
                                        <form>
                                            <div class="form-icon relative">
                                                <i class="ri-linkedin-line absolute top-2 start-3"></i>
                                                <input type="text" class="form-input ps-10 pe-2 py-2 h-10 text-sm rounded-lg w-full bg-transparent border border-gray-100 dark:border-gray-800 focus:shadow-none focus:ring-0 placeholder:text-gray-400 outline-none" placeholder="Linkedin Profile Name" id="linkedin_name" name="name">
                                            </div>
                                        </form>

                                        <p class="text-gray-400 text-sm mt-1">Add your Linkedin username.</p>
                                    </div>
                                </div>
                                
                                <div class="md:flex mt-8">
                                    <div class="md:w-1/3">
                                        <span class="font-medium">Youtube</span>
                                    </div>

                                    <div class="md:w-2/3 mt-4 md:mt-0">
                                        <form>
                                            <div class="form-icon relative">
                                                <i class="ri-youtube-line absolute top-2 start-3"></i>
                                                <input type="url" class="form-input ps-10 pe-2 py-2 h-10 text-sm rounded-lg w-full bg-transparent border border-gray-100 dark:border-gray-800 focus:shadow-none focus:ring-0 placeholder:text-gray-400 outline-none" placeholder="Youtube url" id="you_url" name="url">
                                            </div>
                                        </form>

                                        <p class="text-gray-400 text-sm mt-1">Add your Youtube url.</p>
                                    </div>
                                </div>

                                <div class="md:flex">
                                    <div class="md:w-1/3">
                                        <span class="font-medium"></span>
                                        <button class="h-11 px-5 tracking-wider inline-flex items-center text-xs uppercase justify-center font-bold rounded-lg bg-primary text-white cursor-pointer mt-5">Save Social Profile</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!--end grid-->
            </div><!--end container-->
        </section><!--end section-->
        <!-- End Hero -->

        <?php include 'footer.php';?>

        <!-- Back to top -->
        <a href="#" onclick="topFunction()" id="back-to-top" class="back-to-top fixed hidden text-lg rounded-lg z-10 bottom-5 end-5 size-9 text-center bg-primary/10 hover:bg-primary text-primary hover:text-white leading-9"><i class="ri-arrow-up-line"></i></a>
        <!-- Back to top -->

        <!-- JAVASCRIPTS -->
        <script src="assets/js/plugins.init.js"></script>
        <script src="assets/js/app.js"></script>
        <!-- JAVASCRIPTS -->
    </body>
</html>