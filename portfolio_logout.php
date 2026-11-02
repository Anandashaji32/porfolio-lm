<?php
/**
 * Portfolio Logout Handler
 */
require_once __DIR__ . '/portfolio_db.php';

unset($_SESSION['portfolio_user_id']);
unset($_SESSION['portfolio_user_name']);
unset($_SESSION['portfolio_user_email']);
unset($_SESSION['portfolio_user_role']);

setPortfolioFlash('info', 'You have been signed out from the portfolio portal.');
header('Location: index.php');
exit;
