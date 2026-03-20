// View tutor details
$(document).on("click", ".view-tutor", function (e) {
  e.preventDefault();

  var tutor;
  try {
    tutor = JSON.parse($(this).attr("data-tutor"));
  } catch (err) {
    Swal.fire({
      title: "Error!",
      text: "Unable to load tutor details.",
      icon: "error",
      confirmButtonText: "OK",
    });
    return;
  }

  var profilePic = tutor.profile_pic
    ? "assets/images/tutors/" + tutor.profile_pic
    : "https://via.placeholder.com/150";
  var tutorName = tutor.tutor_name ? tutor.tutor_name : "N/A";
  var tutorEmail = tutor.tutor_email ? tutor.tutor_email : "N/A";
  var tutorPhone = tutor.tutor_phone ? tutor.tutor_phone : "N/A";
  var country = tutor.country ? tutor.country : "N/A";
  var joinDate = tutor.created_at
    ? new Date(tutor.created_at).toLocaleDateString("en-GB", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
      })
    : "N/A";
  var status =
    tutor.tutor_status == 1
      ? '<span style="color:#28a745; font-weight:600;">Active</span>'
      : '<span style="color:#dc3545; font-weight:600;">Inactive</span>';
  var borderColor = tutor.tutor_status == 1 ? "#28a745" : "#dc3545";

  function escapeHtml(value) {
    return String(value ?? "").replace(/[&<>"']/g, function (ch) {
      return {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#39;",
      }[ch];
    });
  }

  var html = `
                    <div style="text-align:left; background:#fff;">
                        <div style="display:flex; gap:24px;">
                            <!-- Left Sidebar -->
                            <div style="flex: 0 0 280px;">
                                <div style="text-align:center; padding-bottom:20px; border-bottom:1px solid #e5e7eb;">
                                    <img src="${escapeHtml(
                                      profilePic
                                    )}" alt="Tutor" style="width:120px; height:120px; border-radius:50%; object-fit:cover; border:3px solid ${borderColor}; margin-bottom:12px;">
                                    <h5 style="margin:10px 0; color:#1a202c; font-weight:600;">${escapeHtml(
                                      tutorName
                                    )}</h5>
                                    <p style="margin:5px 0; color:#6c757d; font-size:13px;">Tutor ID: <strong>${escapeHtml(
                                      tutor.tutor_id
                                    )}</strong></p>
                                </div>
                                <div style="padding-top:16px;">
                                    <p style="font-size:12px; color:#6c757d; text-transform:uppercase; font-weight:600; margin-bottom:12px;">Contact Info</p>
                                    <p style="margin:8px 0; font-size:13px;"><strong>Email:</strong><br><span style="color:#6c757d; word-break:break-all;">${escapeHtml(
                                      tutorEmail
                                    )}</span></p>
                                    <p style="margin:12px 0; font-size:13px;"><strong>Mobile:</strong><br><span style="color:#6c757d;">${escapeHtml(
                                      tutorPhone
                                    )}</span></p>
                                    <p style="margin:12px 0; font-size:13px;"><strong>Country:</strong><br><span style="color:#6c757d;">${escapeHtml(
                                      country
                                    )}</span></p>
                                </div>
                            </div>

                            <!-- Right Content -->
                            <div style="flex:1;">
                                <div style="padding:0;">
                                    <h6 style="color:#1a202c; font-weight:600; margin-bottom:16px; font-size:14px; text-transform:uppercase; color:#6c757d;">Profile Information</h6>
                                    
                                    <div style="margin-bottom:16px;">
                                        <div style="display:flex; justify-content:space-between; padding-bottom:8px; border-bottom:1px solid #f0f0f0;">
                                            <span style="color:#6c757d; font-size:13px;">Full Name</span>
                                            <span style="font-weight:600; color:#1a202c;">${escapeHtml(
                                              tutorName
                                            )}</span>
                                        </div>
                                    </div>

                                    <div style="margin-bottom:16px;">
                                        <div style="display:flex; justify-content:space-between; padding-bottom:8px; border-bottom:1px solid #f0f0f0;">
                                            <span style="color:#6c757d; font-size:13px;">Email Address</span>
                                            <span style="font-weight:600; color:#1a202c; word-break:break-all;">${escapeHtml(
                                              tutorEmail
                                            )}</span>
                                        </div>
                                    </div>

                                    <div style="margin-bottom:16px;">
                                        <div style="display:flex; justify-content:space-between; padding-bottom:8px; border-bottom:1px solid #f0f0f0;">
                                            <span style="color:#6c757d; font-size:13px;">Phone Number</span>
                                            <span style="font-weight:600; color:#1a202c;">${escapeHtml(
                                              tutorPhone
                                            )}</span>
                                        </div>
                                    </div>

                                    <div style="margin-bottom:16px;">
                                        <div style="display:flex; justify-content:space-between; padding-bottom:8px; border-bottom:1px solid #f0f0f0;">
                                            <span style="color:#6c757d; font-size:13px;">Country</span>
                                            <span style="font-weight:600; color:#1a202c;">${escapeHtml(
                                              country
                                            )}</span>
                                        </div>
                                    </div>

                                    <div style="margin-bottom:16px;">
                                        <div style="display:flex; justify-content:space-between; padding-bottom:8px; border-bottom:1px solid #f0f0f0;">
                                            <span style="color:#6c757d; font-size:13px;">Join Date</span>
                                            <span style="font-weight:600; color:#1a202c;">${joinDate}</span>
                                        </div>
                                    </div>

                                    <div style="margin-bottom:0;">
                                        <div style="display:flex; justify-content:space-between; padding-bottom:8px; border-bottom:none;">
                                            <span style="color:#6c757d; font-size:13px;">Status</span>
                                            <span>${status}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

  Swal.fire({
    title: tutorName,
    html: html,
    width: 800,
    showCloseButton: true,
    confirmButtonText: "Close",
    confirmButtonColor: "#3085d6",
    didOpen: function () {
      // Optional: Add custom styling to the modal
      document.querySelector(".swal2-popup").style.borderRadius = "8px";
    },
  });
});

// View user details
$(document).on("click", ".view-user", function (e) {
  e.preventDefault();

  var user;
  try {
    user = JSON.parse($(this).attr("data-user"));
  } catch (err) {
    Swal.fire({
      title: "Error!",
      text: "Unable to load user details.",
      icon: "error",
      confirmButtonText: "OK",
    });
    return;
  }

  var profilePic = user.user_profile_pic
    ? "assets/images/users/" + user.user_profile_pic
    : "https://via.placeholder.com/150";
  var userName = user.user_name ? user.user_name : "N/A";
  var userEmail = user.user_email ? user.user_email : "N/A";
  var userPhone = user.mobile ? user.mobile : "N/A";
  var joinDate = user.user_created_at
    ? new Date(user.user_created_at).toLocaleDateString("en-GB", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
      })
    : "N/A";
  var status =
    user.user_status == 1
      ? '<span style="color:#28a745; font-weight:600;">Active</span>'
      : '<span style="color:#dc3545; font-weight:600;">Inactive</span>';
  var borderColor = user.user_status == 1 ? "#28a745" : "#dc3545";

  function escapeHtml(value) {
    return String(value ?? "").replace(/[&<>"']/g, function (ch) {
      return {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#39;",
      }[ch];
    });
  }

  var html = `
            <div style="text-align:left; background:#fff;">
                <div style="display:flex; gap:24px;">
                    <!-- Left Sidebar -->
                    <div style="flex: 0 0 280px;">
                        <div style="text-align:center; padding-bottom:20px; border-bottom:1px solid #e5e7eb;">
                            <img src="${escapeHtml(
                              profilePic
                            )}" alt="User" style="width:120px; height:120px; border-radius:50%; object-fit:cover; border:3px solid ${borderColor}; margin-bottom:12px;">
                            <h5 style="margin:10px 0; color:#1a202c; font-weight:600;">${escapeHtml(
                              userName
                            )}</h5>
                            <p style="margin:5px 0; color:#6c757d; font-size:13px;">User ID: <strong>${escapeHtml(
                              user.user_id
                            )}</strong></p>
                        </div>
                        <div style="padding-top:16px;">
                            <p style="font-size:12px; color:#6c757d; text-transform:uppercase; font-weight:600; margin-bottom:12px;">Contact Info</p>
                            <p style="margin:8px 0; font-size:13px;"><strong>Email:</strong><br><span style="color:#6c757d; word-break:break-all;">${escapeHtml(
                              userEmail
                            )}</span></p>
                            <p style="margin:12px 0; font-size:13px;"><strong>Mobile:</strong><br><span style="color:#6c757d;">${escapeHtml(
                              userPhone
                            )}</span></p>
                        </div>
                    </div>

                    <!-- Right Content -->
                    <div style="flex:1;">
                        <div style="padding:0;">
                            <h6 style="color:#1a202c; font-weight:600; margin-bottom:16px; font-size:14px; text-transform:uppercase; color:#6c757d;">Profile Information</h6>
                            
                            <div style="margin-bottom:16px;">
                                <div style="display:flex; justify-content:space-between; padding-bottom:8px; border-bottom:1px solid #f0f0f0;">
                                    <span style="color:#6c757d; font-size:13px;">Full Name</span>
                                    <span style="font-weight:600; color:#1a202c;">${escapeHtml(
                                      userName
                                    )}</span>
                                </div>
                            </div>

                            <div style="margin-bottom:16px;">
                                <div style="display:flex; justify-content:space-between; padding-bottom:8px; border-bottom:1px solid #f0f0f0;">
                                    <span style="color:#6c757d; font-size:13px;">Email Address</span>
                                    <span style="font-weight:600; color:#1a202c; word-break:break-all;">${escapeHtml(
                                      userEmail
                                    )}</span>
                                </div>
                            </div>

                            <div style="margin-bottom:16px;">
                                <div style="display:flex; justify-content:space-between; padding-bottom:8px; border-bottom:1px solid #f0f0f0;">
                                    <span style="color:#6c757d; font-size:13px;">Phone Number</span>
                                    <span style="font-weight:600; color:#1a202c;">${escapeHtml(
                                      userPhone
                                    )}</span>
                                </div>
                            </div>

                            <div style="margin-bottom:16px;">
                                <div style="display:flex; justify-content:space-between; padding-bottom:8px; border-bottom:1px solid #f0f0f0;">
                                    <span style="color:#6c757d; font-size:13px;">Join Date</span>
                                    <span style="font-weight:600; color:#1a202c;">${joinDate}</span>
                                </div>
                            </div>

                            <div style="margin-bottom:0;">
                                <div style="display:flex; justify-content:space-between; padding-bottom:8px; border-bottom:none;">
                                    <span style="color:#6c757d; font-size:13px;">Status</span>
                                    <span>${status}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

  Swal.fire({
    title: userName,
    html: html,
    width: 800,
    showCloseButton: true,
    confirmButtonText: "Close",
    confirmButtonColor: "#28a745",
    didOpen: function () {
      document.querySelector(".swal2-popup").style.borderRadius = "8px";
    },
  });
});

// View course details
$(document).on("click", ".view-course", function (e) {
  e.preventDefault();

  var course;
  try {
    course = JSON.parse($(this).attr("data-course"));
    category = JSON.parse($(this).attr("data-category"));
  } catch (err) {
    Swal.fire({
      title: "Error!",
      text: "Unable to load course details.",
      icon: "error",
      confirmButtonText: "OK",
    });
    return;
  }

  var courseName = course.course_title ? course.course_title : "N/A";
  var courseDesc = course.course_description
    ? course.course_description
    : "No description available";
  var category_name = category.category_name ? category.category_name : "N/A";
  var level = course.course_level ? course.course_level : "N/A";
  var status =
    course.course_status == 1
      ? '<span style="color:#28a745; font-weight:600;">Active</span>'
      : '<span style="color:#dc3545; font-weight:600;">Inactive</span>';
  var price = course.price ? "₹" + course.price : "Free";
  var total_lessons = course.total_lesson ? course.total_lesson : "N/A";

  function escapeHtml(value) {
    return String(value ?? "").replace(/[&<>"']/g, function (ch) {
      return {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#39;",
      }[ch];
    });
  }

  var html = `
            <div style="text-align:left; background:#fff; padding:20px;">
                <div style="margin-bottom:24px;">
                    <h5 style="color:#1a202c; font-weight:600; margin-bottom:12px; font-size:18px;">${escapeHtml(
                      courseName
                    )}</h5>
                    <p style="color:#6c757d; font-size:14px; line-height:1.6; margin:0;">${escapeHtml(
                      courseDesc
                    )}</p>
                </div>

                <div style="border-top:1px solid #e5e7eb; padding-top:20px;">
                    <h6 style="color:#1a202c; font-weight:600; margin-bottom:16px; font-size:14px; text-transform:uppercase; color:#6c757d;">Course Details</h6>
                    
                    <div style="margin-bottom:16px;">
                        <div style="display:flex; justify-content:space-between; padding-bottom:8px; border-bottom:1px solid #f0f0f0;">
                            <span style="color:#6c757d; font-size:13px;">Course ID</span>
                            <span style="font-weight:600; color:#1a202c;">${escapeHtml(
                              course.course_id
                            )}</span>
                        </div>
                    </div>

                    <div style="margin-bottom:16px;">
                        <div style="display:flex; justify-content:space-between; padding-bottom:8px; border-bottom:1px solid #f0f0f0;">
                            <span style="color:#6c757d; font-size:13px;">Course Title</span>
                            <span style="font-weight:600; color:#1a202c;">${escapeHtml(
                              courseName
                            )}</span>
                        </div>
                    </div>
                    <div style="margin-bottom:16px;">
                        <div style="display:flex; justify-content:space-between; padding-bottom:8px; border-bottom:1px solid #f0f0f0;">
                            <span style="color:#6c757d; font-size:13px;">Category</span>
                            <span style="font-weight:600; color:#1a202c;">${escapeHtml(
                              category_name
                            )}</span>
                        </div>
                    </div>
                    <div style="margin-bottom:16px;">
                        <div style="display:flex; justify-content:space-between; padding-bottom:8px; border-bottom:1px solid #f0f0f0;">
                            <span style="color:#6c757d; font-size:13px;">Level</span>
                            <span style="font-weight:600; color:#1a202c;">${escapeHtml(
                              level
                            )}</span>
                        </div>
                    </div>
                    <div style="margin-bottom:16px;">
                        <div style="display:flex; justify-content:space-between; padding-bottom:8px; border-bottom:1px solid #f0f0f0;">
                            <span style="color:#6c757d; font-size:13px;">Status</span>
                            <span>${status}</span>
                        </div>
                    </div>
                    <div style="margin-bottom:16px;">
                        <div style="display:flex; justify-content:space-between; padding-bottom:8px; border-bottom:1px solid #f0f0f0;">
                            <span style="color:#6c757d; font-size:13px;">Price</span>
                            <span style="font-weight:600; color:#1a202c;">${price}</span>
                        </div>
                    </div>
                    <div style="margin-bottom:16px;">
                        <div style="display:flex; justify-content:space-between; padding-bottom:8px; border-bottom:none;">
                            <span style="color:#6c757d; font-size:13px;">Total Lessons</span>
                            <span style="font-weight:600; color:#1a202c;">${total_lessons}</span>
                        </div>
                    </div>

                </div>
            </div>
        `;

  Swal.fire({
    title: courseName,
    html: html,
    width: 800,
    showCloseButton: true,
    confirmButtonText: "Close",
    confirmButtonColor: "#188AE2",
    didOpen: function () {
      document.querySelector(".swal2-popup").style.borderRadius = "8px";
    },
  });
});
