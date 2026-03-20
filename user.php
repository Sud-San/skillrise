<div class="container relative">
    <div class="p-6 bg-white dark:bg-gray-900 rounded-lg shadow-sm shadow-gray-100 dark:shadow-gray-800">
        <div class="grid md:grid-cols-12 items-center gap-6">
            <div class="lg:col-span-4 md:col-span-6">
                <div class="md:flex md:items-center md:text-start text-center">
                    <div class="profile-pic">
                        <input id="pro-img" name="profile-image" type="file" class="hidden"
                            onchange="loadFile(event)" />
                        <div class="relative h-30 w-28 mx-auto">

                            <?php
                            $display_pic = $_SESSION['user_profile_pic'];
                            $display_name = $user['user_name'] ?? 'User';
                            $quote = [
                                "Improving every single day ✨",
                                "The only way to do great work is to love what you do.",
                                "Success is not final, failure is not fatal: it is the courage to continue that counts.",
                                "Believe you can and you're halfway there.",
                                "The future belongs to those who believe in the beauty of their dreams.",
                                "It always seems impossible until it's done.",
                                "Strive not to be a success, but rather to be of value.",
                                "The mind is everything. What you think you become.",
                                "The best way to predict the future is to create it.",
                                "Success is walking from failure to failure with no loss of enthusiasm."
                            ];
                            ?>

                            <img src="<?php echo $display_pic; ?>"
                                class="w-28 h-28 rounded-full shadow-sm shadow-gray-100 dark:shadow-gray-800 ring-4 ring-gray-50 dark:ring-gray-800"
                                id="profile-image" alt="<?php echo $display_name; ?>">

                            <label class="absolute inset-0 cursor-pointer" for="pro-img"></label>
                        </div>
                    </div>

                    <div class="md:mt-0 md:ms-4 mt-4">
                        <h5 class="text-lg font-semibold"><?php echo $display_name; ?></h5>
                        <p class="text-gray-400"><?php echo $user['user_email'] ?? ''; ?></p>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4 md:col-span-6">
                <p class="text-primary font-medium text-sm italic text-center w-full flex justify-center">
                    “
                    <?php echo $quote[array_rand($quote)]; ?>
                    ”
                </p>
            </div>

            <!-- Social icons -->
            <div class="flex justify-center md:justify-start gap-3 mt-3">
                <a href="#" class="text-gray-500 hover:text-primary transition"><i
                        class="uil uil-facebook-f text-lg"></i></a>
                <a href="#" class="text-gray-500 hover:text-primary transition"><i
                        class="uil uil-instagram text-lg"></i></a>
                <a href="#" class="text-gray-500 hover:text-primary transition"><i
                        class="uil uil-linkedin text-lg"></i></a>
            </div>

            <div class="grid lg:grid-cols-12 grid-cols-1 items-center gap-6">
                <div class="lg:col-span-12 flex flex-wrap items-center gap-2">

                    <span
                        class="px-4 py-2 text-xs font-medium rounded-full bg-primary/10 text-primary inline-flex items-center justify-center">
                        ⭐ Verified User
                    </span>

                    <span
                        class="px-3 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-800 dark:text-white inline-flex items-center justify-center">
                        Member Since • <?php echo date("Y", strtotime($user['created_at'])); ?>
                    </span>

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

                <p class="text-gray-400 text-sm mt-2">
                    <?php echo !empty($aboutme) ? e($aboutme) : 'No bio added yet.'; ?>
                </p>

                <div class="mt-4">

                    <div class="flex items-center">
                        <i class="ri-mail-line text-primary text-xl me-2.5"></i>
                        <div class="flex-1">
                            <h6 class="font-semibold text-sm mb-0">Email :</h6>
                            <a href="mailto:<?php echo e($user['user_email'] ?? ''); ?>" class="text-gray-400 text-sm">
                                <?php echo e($user['user_email'] ?? '-'); ?>
                            </a>
                        </div>
                    </div>

                    <div class="flex items-center mt-3">
                        <i class="ri-user-line text-primary text-xl me-2.5"></i>
                        <div class="flex-1">
                            <h6 class="font-semibold text-sm mb-0">Gender :</h6>
                            <span
                                class="text-gray-400 text-sm"><?php echo !empty($user['gender']) ? ucfirst($user['gender']) : '-'; ?></span>
                        </div>
                    </div>

                    <div class="flex items-center mt-3">
                        <i class="ri-gift-line text-primary text-xl me-2.5"></i>
                        <div class="flex-1">
                            <h6 class="font-semibold text-sm mb-0">Birthday :</h6>
                            <p class="text-gray-400 text-sm mb-0">
                                <?php echo !empty($user['dob']) ? e(date("d M Y", strtotime($user['dob']))) : '-'; ?>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center mt-3">
                        <i class="ri-map-pin-line text-primary text-xl me-2.5"></i>
                        <div class="flex-1">
                            <h6 class="font-semibold text-sm mb-0">City :</h6>
                            <span <?php
                            $str = "select city_name from city_tbl where city_id=" . $user["city"] . ";";
                            $res = mysqli_query($conn, $str);
                            $row = mysqli_fetch_assoc($res);
                            ?>
                                class="text-gray-400 text-sm"><?php echo !empty($row['city_name']) ? e($row['city_name']) : '-'; ?></span>
                        </div>
                    </div>

                    <div class="flex items-center mt-3">
                        <i class="ri-phone-line text-primary text-xl me-2.5"></i>
                        <div class="flex-1">
                            <h6 class="font-semibold text-sm mb-0">Cell No :</h6>

                            <?php if (!empty($user['mobile'])) {
                                $displayPhone = '+91 ' . $user['mobile'];
                                $telPhone = '+91' . $user['mobile'];
                                ?>
                                <a href="tel:<?php echo e($telPhone); ?>" class="text-gray-400 text-sm">
                                    <?php echo e($displayPhone); ?>
                                </a>
                            <?php } else { ?>
                                <span class="text-gray-400 text-sm">-</span>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="flex items-center mt-3">
                        <i class="ri-italic text-primary text-xl me-2.5"></i>
                        <div class="flex-1">
                            <h6 class="font-semibold text-sm mb-0">Joined :</h6>
                            <span class="text-gray-400 text-sm">
                                <?php echo !empty($user['created_at']) ? date("d M Y", strtotime($user['created_at'])) : '-'; ?>
                            </span>
                        </div>
                    </div>

                </div>
            </div>
        </div><!--end col-->