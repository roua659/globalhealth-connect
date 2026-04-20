<?php
require_once 'config/database.php';

class AuthController {

    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    // ──────────────────────────────────────────
    // Afficher le formulaire + traiter la connexion
    // ──────────────────────────────────────────
    public function login() {
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email']    ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($email) || empty($password)) {
                $error = "Veuillez remplir tous les champs.";
            } else {
                // Essayer d'abord avec le mot de passe en clair (texte direct)
                $stmt = $this->pdo->prepare("
                    SELECT u.*, r.type_role AS role
                    FROM utilisateur u
                    JOIN role r ON u.id_role = r.id_role
                    WHERE u.email = ? AND (u.mot_de_passe = ? OR u.mot_de_passe = MD5(?))
                ");
                $stmt->execute([$email, $password, $password]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    $_SESSION['user_id']   = $user['id_user'];
                    $_SESSION['user_name'] = $user['nom'] . ' ' . $user['prenom'];
                    $_SESSION['user_role'] = $user['role'];

                    if ($user['role'] === 'admin') {
                        // Admin → backoffice admin (gestion globale)
                        header('Location: ?controller=admin');
                        exit();

                    } elseif ($user['role'] === 'medecin') {
                        // Médecin → backoffice médecin (ajouter consultations & suivis)
                        $m = $this->pdo->prepare("SELECT id_medecin FROM medecin WHERE id_user = ?");
                        $m->execute([$user['id_user']]);
                        $med = $m->fetch(PDO::FETCH_ASSOC);
                        if ($med) {
                            $_SESSION['medecin_id'] = $med['id_medecin'];
                        }
                        header('Location: ?controller=medecin');
                        exit();

                    } elseif ($user['role'] === 'patient') {
                        // Patient → backoffice patient (lecture seule de ses données)
                        $p = $this->pdo->prepare("SELECT id_patient FROM patient WHERE id_user = ?");
                        $p->execute([$user['id_user']]);
                        $pat = $p->fetch(PDO::FETCH_ASSOC);
                        if ($pat) {
                            $_SESSION['patient_id'] = $pat['id_patient'];
                        }
                        header('Location: ?controller=patient');
                        exit();

                    } else {
                        header('Location: ?controller=front');
                        exit();
                    }
                } else {
                    $error = "Email ou mot de passe incorrect.";
                }
            }
        }

        require_once 'views/login.php';
    }

    // ──────────────────────────────────────────
    // Déconnexion
    // ──────────────────────────────────────────
    public function logout() {
        session_destroy();
        header('Location: ?controller=front');
        exit();
    }
}
?>
