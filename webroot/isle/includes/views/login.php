
<!-- Code taken from:
http://www.alessioatzeni.com/blog/login-box-modal-dialog-window-with-css-and-jquery/
-->

<div id="login-box" class="login-popup">
    <!-- <form method="post" class="login" action="" name="login" id="login"> -->
    <form method="post" class="login" action="" name="modalForm" id="modalForm" style="text-align: center;">
        <!-- <fieldset class="textbox" style="text-align: center;"> -->
        <table style="border-spacing: 2px; margin-right:auto; margin-left:auto">
            <caption style="text-align: center;">Please Login:</caption>
            <tr>
                <td style="padding: 15px; text-align: right;">Username</td>
                <td style="padding: 15px;" >
                    <input id="username" name="username" value="" type="text"
                           autocomplete="on" placeholder="Username">
                </td>
            </tr>
            <tr>
                <td style="padding: 15px; text-align: right;">Password</td>
                <td style="padding: 15px;" >
                    <input id="password" name="password" value="" type="password"
                           placeholder="Password">
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <button class="submit button" type="submit">Login</button>
                </td>
            </tr>
        </table>
    </form>
</div>

