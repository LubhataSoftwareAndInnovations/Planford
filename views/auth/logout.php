<?php
if (isLoggedIn()) {
    audit('logout', 'users', authId(), 'User signed out');
}
session_destroy();
redirect('/login', '', '');
