<?php
/**
 * Gestion des sessions - GlobalHealth Connect
 */

class Session {
    
    /**
     * Démarre la session si elle n'est pas déjà active
     */
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Définit une valeur en session
     */
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Récupère une valeur de session
     */
    public static function get($key, $default = null) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }
    
    /**
     * Vérifie si une clé existe en session
     */
    public static function has($key) {
        return isset($_SESSION[$key]);
    }
    
    /**
     * Supprime une valeur de session
     */
    public static function remove($key) {
        unset($_SESSION[$key]);
    }
    
    /**
     * Détruit la session
     */
    public static function destroy() {
        $_SESSION = array();
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
    }
    
    /**
     * Définit un message flash
     */
    public static function setFlash($key, $message) {
        $_SESSION['flash_' . $key] = $message;
    }
    
    /**
     * Récupère et supprime un message flash
     */
    public static function getFlash($key) {
        $flashKey = 'flash_' . $key;
        $message = isset($_SESSION[$flashKey]) ? $_SESSION[$flashKey] : null;
        unset($_SESSION[$flashKey]);
        return $message;
    }
    
    /**
     * Vérifie si un message flash existe
     */
    public static function hasFlash($key) {
        return isset($_SESSION['flash_' . $key]);
    }
    
    /**
     * Régénère l'ID de session (sécurité)
     */
    public static function regenerate() {
        session_regenerate_id(true);
    }
    
    /**
     * Définit l'ID utilisateur
     */
    public static function setUserId($id) {
        $_SESSION['user_id'] = $id;
    }
    
    /**
     * Récupère l'ID utilisateur
     */
    public static function getUserId() {
        return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    }
    
    /**
     * Définit le rôle utilisateur
     */
    public static function setUserRole($role) {
        $_SESSION['user_role'] = $role;
    }
    
    /**
     * Récupère le rôle utilisateur
     */
    public static function getUserRole() {
        return isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
    }
    
    /**
     * Vérifie si l'utilisateur est connecté
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Vérifie si l'utilisateur est admin
     */
    public static function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
}
?>