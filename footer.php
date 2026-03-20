 <footer class="relative bg-gray-900 dark:bg-gray-800 border-t border-gray-800 dark:border-gray-700">
            <div class="container relative">
                <div class="grid grid-cols-1">
                    <div class="relative py-8">
                        <div class="relative">
                            <div class="grid md:grid-cols-2 grid-cols-1 items-center gap-6">
                                <div class="md:text-start text-center z-1">
                                    <h3 class="text-2xl md:leading-normal leading-normal font-medium text-gray-100 dark:text-white">Join Our Newsletter!</h3>
                                    <p class="text-gray-300 max-w-xl mx-auto">Subscribe to get latest updates and information.</p>
                                </div>

                                <div class="subcribe-form z-1">
                                    <form class="relative max-w-lg md:ms-auto" onsubmit="event.preventDefault(); window.location.href='signup.php';">
                                        <input type="email" id="subemail" name="name" class="p-4 pe-40 w-full h-[50px] outline-none bg-transparent border border-gray-800 dark:border-gray-700 text-gray-100 rounded-lg" placeholder="Enter your email id..">
                                        <button type="submit" onclick="window.location.href='signup.php'" class="h-11 px-4 tracking-wider justify-center items-center text-sm font-medium rounded-lg bg-primary text-white absolute top-0.75 end-0.75 cursor-pointer">Subcribe Now</button>
                                    </form><!--end form-->
                                </div>
                            </div>

                            <div class="absolute -top-5 -start-5">
                                <div class="uil uil-envelope lg:text-[150px] text-7xl text-black/5 dark:text-white/5 ltr:-rotate-45 rtl:rotate-45"></div>
                            </div>

                            <div class="absolute -bottom-5 -end-5">
                                <div class="uil uil-pen lg:text-[150px] text-7xl text-black/5 dark:text-white/5 rtl:-rotate-90"></div>
                            </div>
                        </div>
                    </div>
                </div><!--end container-->

                <div class="py-8 border-t border-gray-800 dark:border-gray-700">
                    <div class="container relative">
                        <!-- Subscribe -->
                        <div class="relative w-full">
                            <div class="grid md:grid-cols-12 grid-cols-1 gap-6">
                                <div class="lg:col-span-4 md:col-span-12">
                                    <a href="index.php" class="text-[22px] focus:outline-none">
                                       <img src="<?php echo $logo; ?>"  height="150px" width="300px" class="l-light"  alt="">
                                    </a>
                                    <p class="mt-6 text-gray-300"><?php echo $company_name; ?> offers a more personalized learning experience along with the flexibility of learning at your own pace.</p>
                                </div><!--end col-->
                        
                               <div class="lg:col-span-2 md:col-span-3">
                                    <h5 class="tracking-[1px] text-gray-100 font-medium text-lg">Course</h5>
                            
                                    <ul class="list-none footer-list mt-6">
                                        <li><i class="ri-arrow-right-s-line text-gray-400 me-1"></i><a href="courses.php" class="text-gray-300 hover:text-gray-400 duration-500 ease-in-out">Popular Course</a></li>
                                        <!--<li class="mt-[10px]"><i class="ri-arrow-right-s-line text-gray-400 me-1"></i><a href="course-detail.php" class="text-gray-300 hover:text-gray-400 duration-500 ease-in-out">Course Details</a></li>-->
                                    </ul>
                                </div><!--end col-->
                        
                                <div class="lg:col-span-2 md:col-span-3">
                                    <h5 class="tracking-[1px] text-gray-100 font-medium text-lg">Company</h5>

                                    <ul class="list-none footer-list mt-6">
                                        <li><i class="ri-arrow-right-s-line text-gray-400 me-1"></i><a href="aboutus.php" class="text-gray-300 hover:text-gray-400 duration-500 ease-in-out">About Us</a></li>
                                        <li class="mt-[10px]"><i class="ri-arrow-right-s-line text-gray-400 me-1"></i><a href="terms.php" class="text-gray-300 hover:text-gray-400 duration-500 ease-in-out">Terms of </br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Services</a></li>
                                        <li class="mt-[10px]"><i class="ri-arrow-right-s-line text-gray-400 me-1"></i><a href="privacy.php" class="text-gray-300 hover:text-gray-400 duration-500 ease-in-out">Privacy Policy</a></li>
                                    </ul>
                                </div><!--end col-->
    
                                <div class="lg:col-span-2 md:col-span-6">
                                    <h5 class="tracking-[1px] text-gray-100 font-medium text-lg">Pages</h5>

                                    <ul class="list-none footer-list mt-6">
                                        <!--<li><i class="ri-arrow-right-s-line text-gray-400 me-1"></i><a href="pricing.php" class="text-gray-300 hover:text-gray-400 duration-500 ease-in-out">Pricing</a></li>-->
                                        <li class="mt-[10px]"><i class="ri-arrow-right-s-line text-gray-400 me-1"></i><a href="team.php" class="text-gray-300 hover:text-gray-400 duration-500 ease-in-out">Team</a></li>
                                        <li class="mt-[10px]"><i class="ri-arrow-right-s-line text-gray-400 me-1"></i><a href="faqs.php" class="text-gray-300 hover:text-gray-400 duration-500 ease-in-out">FAQs</a></li>
                                    </ul>
                                </div><!--end col-->
    
                                <div class="lg:col-span-2 md:col-span-6">
                                    <h5 class="tracking-[1px] text-gray-100 font-medium text-lg">Authentication</h5>

                                    <ul class="list-none footer-list mt-6">
                                        <li><i class="ri-arrow-right-s-line text-gray-400 me-1"></i><a href="login.php" class="text-gray-300 hover:text-gray-400 duration-500 ease-in-out">Login</a></li>
                                        <li class="mt-[10px]"><i class="ri-arrow-right-s-line text-gray-400 me-1"></i><a href="signup.php" class="text-gray-300 hover:text-gray-400 duration-500 ease-in-out">Signup</a></li>
                                    </ul>
                                </div><!--end col-->
								
                            </div><!--end grid-->
                        </div>
                        <!-- Subscribe -->
                    </div>
                </div>
            </div><!--end container-->

           <!-- <div class="py-8 px-0 border-t border-gray-800 dark:border-gray-700">
                <div class="container relative text-center">
                    <div class="grid md:grid-cols-2 items-center gap-6">
                       

                        <ul class="list-none md:text-end text-center">
                            <li class="inline"><a href="https://dribbble.com/shreethemes" target="_blank" class="size-7 inline-flex justify-center items-center text-gray-400 hover:text-white border border-gray-800 dark:border-gray-700 rounded-lg hover:border-primary dark:hover:border-primary hover:bg-primary dark:hover:bg-primary"><i class="ri-dribbble-line"></i></a></li>
                            <li class="inline"><a href="https://www.behance.net/shreethemes" target="_blank" class="size-7 inline-flex justify-center items-center text-gray-400 hover:text-white border border-gray-800 dark:border-gray-700 rounded-lg hover:border-primary dark:hover:border-primary hover:bg-primary dark:hover:bg-primary"><i class="ri-behance-line"></i></a></li>
                            <li class="inline"><a href="http://linkedin.com/company/shreethemes" target="_blank" class="size-7 inline-flex justify-center items-center text-gray-400 hover:text-white border border-gray-800 dark:border-gray-700 rounded-lg hover:border-primary dark:hover:border-primary hover:bg-primary dark:hover:bg-primary"><i class="ri-linkedin-line"></i></a></li>
                            <li class="inline"><a href="https://www.facebook.com/shreethemes" target="_blank" class="size-7 inline-flex justify-center items-center text-gray-400 hover:text-white border border-gray-800 dark:border-gray-700 rounded-lg hover:border-primary dark:hover:border-primary hover:bg-primary dark:hover:bg-primary"><i class="ri-facebook-circle-line"></i></a></li>
                            <li class="inline"><a href="https://www.instagram.com/shreethemes/" target="_blank" class="size-7 inline-flex justify-center items-center text-gray-400 hover:text-white border border-gray-800 dark:border-gray-700 rounded-lg hover:border-primary dark:hover:border-primary hover:bg-primary dark:hover:bg-primary"><i class="ri-instagram-line"></i></a></li>
                            <li class="inline"><a href="https://twitter.com/shreethemes" target="_blank" class="size-7 inline-flex justify-center items-center text-gray-400 hover:text-white border border-gray-800 dark:border-gray-700 rounded-lg hover:border-primary dark:hover:border-primary hover:bg-primary dark:hover:bg-primary"><i class="ri-twitter-x-line"></i></a></li>
                            <li class="inline"><a href="mailto:support@shreethemes.in" class="size-7 inline-flex justify-center items-center text-gray-400 hover:text-white border border-gray-800 dark:border-gray-700 rounded-lg hover:border-primary dark:hover:border-primary hover:bg-primary dark:hover:bg-primary"><i class="ri-mail-line"></i></a></li>
                        </ul><!--end icon-->
                    </div><!--end grid-->
                </div><!--end container-->
				</div>-->
        </footer><!--end footer-->
        <!-- End Footer -->

        <!-- SweetAlert2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
