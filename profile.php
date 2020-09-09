<?php
include 'header.php';

$message_obj = new Message($con, $userLogin);
if(isset($_GET['profile_username'])){
    $username =$_GET['profile_username'];
    $user_details_query = mysqli_query($con, "select * from users where user_name = '$username'");
    $user_array = mysqli_fetch_array($user_details_query);

    $num_friends = (substr_count($user_array['friend_array'],","))-1;
}
if(isset($_POST['remove_friend'])){
    $user = new User($con, $userLogin);
    $user->removeFriend($username);
}

if(isset($_POST['add_friend'])){
    $user = new User($con, $userLogin);
    $user->sendRequest($username);
}
if(isset($_POST['respond_request'])){
    header("Location: friend_requests.php");
}
if(isset($_POST['post_message'])){
    if(isset($_POST['message_body'])){
        $body = mysqli_real_escape_string($con, $_POST['message_body']);
        $date = date("Y-m-d H:i:s");
        $message_obj->sendMessage($username,$body,$date);
    }
    $link = '#profileTabs a[href="#messages_div"]';
    echo "<script>
            $(function() {
              $('".$link."').tab('show');
            });
        </script>";
}

if(!$logged_in_bool){
    header("Location: register.php");
}


?>

    <style type="text/css">
        .wrapper{
            margin-left: 0;
            padding-left: 0;
        }

    </style>

    <div class="container account_wrapper">
        <div class="row">
            <div class="col-lg-4">
                <div class="profile_left column">
                    <img src="<?php echo $user_array['profile_picture'];?>">
                    <div class="profile_info">
                        <p><?php echo "Posts: " . $user_array['num_posts'] ?> </p>
                        <p><?php echo "Friends: " . $num_friends ?> </p>
                    </div>

                    <form action="<?php echo $username; ?>" method="POST">
                        <?php
                        $profile_user_obj = new User($con, $username);
                        if($profile_user_obj->isClosed()){
                            header("Location: user_close.php");
                        }
                        $logged_in_user_obj = new User($con, $userLogin);
                        if($userLogin != $username){

                            if($logged_in_user_obj->isFriend($username)){
                                echo '<input type="submit" name="remove_friend" class="danger" value="Remove Friend"><br>';
                            }
                            elseif ($logged_in_user_obj->didReceiveRequest($username)){
                                echo '<input type="submit" name="respond_request" class="warning" value="Response to Request"><br>';
                            }
                            elseif ($logged_in_user_obj->didSendRequest($username)){
                                echo '<input type="submit" name="" class="default" value="Request Sent"><br>';
                            }
                            else
                                echo '<input type="submit" name="add_friend" class="success" value="Add Friend"><br>';

                        }

                        ?>


                    </form>
                    <input type="submit" class="deep_blue" data-toggle="modal" data-target="#post_form" value="Post Something">

                    <?php
                    if($userLogin != $username){
                        echo '<div class="profile_info_bottom">';
                        echo $logged_in_user_obj->getMutualFriends($username). " Mutual friends";
                        echo '</div>';
                    }

                    ?>

                </div>
            </div>


            <div class="col-lg-8">
                <div class="profile_main_column column">
                    <ul class="nav nav-tabs" role="tablist" id="profileTabs">
                        <li class="nav-item">
                            <a class="nav-link " href="#newsfeed_div" aria-controls="newsfeed_div" role="tab" data-toggle="tab">Personal Wall</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="#about_div" aria-controls="about_div" role="tab" data-toggle="tab">About Me</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#messages_div" aria-controls="messages_div" role="tab" data-toggle="tab">Messages</a>
                        </li>
                    </ul>
                    <br>

                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane show in active" id="newsfeed_div">
                            <div class="posts_area"></div>
                            <img id="loading" src="img/loading.gif">
                        </div>

                        <div role="tabpanel" class="tab-pane fade" id="about_div">
                            <?php
                                echo $profile_user_obj->getAbout($username);
                                if($userLogin == $profile_user_obj->getUsername()){
                                    echo "<br>";
                                    echo "<input type=\"submit\" class=\"deep_blue\" data-toggle=\"modal\" data-target=\"#about_form\" value=\"Edit\">";
                                }
                            ?>
                        </div>

                        <div role="tabpanel" class="tab-pane fade" id="messages_div">
                            <?php

                            echo "<h4> You and <a href='".$username."'>". $profile_user_obj->getFullName()."</a></h4><hr><br>";
                            echo "<div class='loaded_messages' id='scrollable'>";
                            echo $message_obj->getMessages($username);
                            echo"</div>";
                            ?>

                            <div class="message_post">
                                <form action="" method = "POST">

                                    <textarea name='message_body' id='message_textarea' placeholder='your message here...'></textarea>
                                    <input type='submit' name='post_message' class='info' id='message_submit' value='Send'>

                                </form>
                            </div>

                            <script>
                                var div = document.getElementById("scrollable")
                                div.scrollTop = div.scrollHeight;
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal -->
    <!-- Modal -->
    <div class="modal fade" id="post_form" tabindex="-1" role="dialog" aria-labelledby="postModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title" id="postModalLabel">Post something!</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>

                <div class="modal-body">

                    <p>This will appear on the user's profile page and also their newsfeed for your friends to see!</p>
                    <form class="profile_post" action="" method="POST">
                        <div class="form-group">
                            <textarea class="form-control" name="post_body"></textarea>
                            <input type="hidden" name="user_from" value="<?php echo $userLogin; ?>">
                            <input type="hidden" name="user_to" value="<?php echo $username; ?>">
                        </div>
                    </form>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" name="post_button" id="submit_profile_post">Post</button>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal for the about form-->
    <div class="modal fade" id="about_form" tabindex="-1" role="dialog" aria-labelledby="postModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title" id="postModalLabel">About Me</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>

                <div class="modal-body">

                    <p>Tell a story about yourself or something that will grab everyone attention!(words limit: 500)</p>
                    <form class="about_post" action="" method="POST">
                        <div class="form-group">
                            <textarea class="form-control" name="post_body"></textarea>
                            <input type="hidden" name="user_from" value="<?php echo $userLogin; ?>">
<!--                            <input type="hidden" name="user_to" value="--><?php //echo $username; ?><!--">-->
                        </div>
                    </form>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Discard</button>
                    <button type="button" class="btn btn-primary" name="post_button" id="update_about">Update</button>
                </div>

            </div>
        </div>
    </div>

    <script>
        $(function(){

            var userLogin = '<?php echo $userLogin; ?>';
            var profileUsername = '<?php echo $username; ?>';
            var inProgress = false;

            loadPosts(); //Load first posts

            $(window).scroll(function() {
                var bottomElement = $(".status_post").last();
                var noMorePosts = $('.posts_area').find('.noMorePosts').val();

                // isElementInViewport uses getBoundingClientRect(), which requires the HTML DOM object, not the jQuery object. The jQuery equivalent is using [0] as shown below.
                if (isElementInView(bottomElement[0]) && noMorePosts == 'false') {

                    loadPosts();
                }
            });

            function loadPosts() {
                if(inProgress) { //If it is already in the process of loading some posts, just return
                    return;
                }

                inProgress = true;
                $('#loading').show();
                var page = $('.posts_area').find('.nextPage').val() || 1; //If .nextPage couldn't be found, it must not be on the page yet (it must be the first time loading posts), so use the value '1'

                $.ajax({
                    url: "ajax_load_profile_posts.php",
                    type: "POST",
                    data: "page=" + page + "&userLogin=" + userLogin + "&profileUsername=" + profileUsername,
                    cache:false,

                    success: function(response) {
                        $('.posts_area').find('.nextPage').remove(); //Removes current .nextpage
                        $('.posts_area').find('.noMorePosts').remove(); //Removes current .nextpage
                        $('.posts_area').find('.noMorePostsText').remove(); //Removes current .nextpage

                        $('#loading').hide();
                        $(".posts_area").append(response);

                        inProgress = false;
                    }
                });


            }

            //Check if the element is in view
            function isElementInView (el) {
                var rect = el.getBoundingClientRect();

                return (
                    rect.top >= 0 &&
                    rect.left >= 0 &&
                    rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) && //* or $(window).height()
                    rect.right <= (window.innerWidth || document.documentElement.clientWidth) //* or $(window).width()
                );
            }
        });

    </script>


    </div>
    </body>

    </html>


<?php include 'footer.php'?>