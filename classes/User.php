<?php 

/******************************************************************************/
/*                                                                            */
/* Pix : Hébergement d'images                                                 */
/*                                                                            */
/******************************************************************************/
/*                                                                            */
/* Auteur:                                                                    */
/*     - Mickael BLATIERE (mickael@saezlive.net)                              */
/*                                                                            */
/* Contributeurs :                                                            */
/*     - ZeR0^ (zero@toile-libre.org)                                         */
/*     - NiZoX (nizox@alterinet.org)                                          */
/*                                                                            */
/* Licence : GPL                                                              */
/*                                                                            */
/******************************************************************************/


class User {

    private $_pseudo;
    private $_password;
    private $_sessionId;
    public $error;

    public function login($pseudo, $password) {
        $this->_pseudo = htmlspecialchars($pseudo);
        $this->_password = $password;
        $this->_encryptPassword();

        if ($this->_checkLogin($password)) {
            return $this->_login();
        }

        $this->error = "Mauvais pseudo ou mot de passe.";
        return false;
    }

    private function _checkLogin() {
        global $sql;

        $query = "SELECT id FROM users WHERE pseudo='" . $sql->escape($this->_pseudo) . "' AND password='" . $this->_password. "'";
        $sql->execute($query);

        return $sql->count() == 1;
    }

    public function getPseudo() {
        return $this->_pseudo;
    }

    public static function get() {
        global $sql;

        if ($_SESSION['pseudo']) {
            $query = "SELECT * FROM users WHERE pseudo = '" . $sql->escape($_SESSION['pseudo']) . "'";
            $sql->execute($query);
            if ($row = $sql->next()) {
                $user = new User();
                $user->_pseudo = $row['pseudo'];
                return $user;
            }
        }
        return null;
    }

    private function _login() {
        global $sql, $config;

        $sessionId =  md5(uniqid(rand(), true));

        $query = "UPDATE users SET session = '" . $sessionId . "' WHERE pseudo = '" . $sql->escape($this->_pseudo) . "'";
        if ($sql->execute($query)) {
            ob_clean();
            setcookie($config['cookie'], $sessionId, time()+60*60*24*30); // expire: one month
            return true;
        }
        return false;
    }

    public static function checkCookie() {
        global $sql, $config;

        $sessionId = $_COOKIE[$config['cookie']];

        if ($sessionId) {
            $query = "SELECT * FROM users WHERE session = '" . $sql->escape($sessionId) . "'";
            $sql->execute($query);
            if ($row = $sql->next()) {
                $_SESSION['pseudo'] = $row['pseudo'];
                return;
            } 
        }
        User::_logout();
    }

    public static function logout() {
        ob_clean();
        User::_logout();
    }

    private static function _logout() {
        global $config;

        setcookie($config['cookie'], '');
        $_SESSION['pseudo'] = '';
        session_destroy();
    }

    public function register($pseudo, $password, $confirm) {
        global $sql;

        $this->_pseudo = htmlspecialchars($pseudo);
        $this->_password = $password;

        // check pseudo
        if (!$this->_checkPseudo()) {
            $this->error = "Votre pseudo doit faire plus de 3 caract&egrave;res.";
            return false;
        }

        if ($password != $confirm) {
            $this->error = "Les deux mots de passe sont diff&eacute;rent.";
            return false;
        }

        // check password
        if (!$this->_checkPassword()) {
            $this->error = "Votre mot de passe doit faire plus de 5 caract&egrave;res.";
            return false;
        }
        $this->_encryptPassword();

        if (!$this->_checkPseudoIsFree()) {
            $this->error = "Ce pseudo est d&eacute;j&agrave; prit.";
            return false;
        }

        $query = "INSERT INTO users (pseudo, password, ip)  VALUES('" . $sql->escape($this->_pseudo) . "', '" . $this->_password . "', '" . $_SERVER['REMOTE_ADDR'] . "')";
        if ($sql->execute($query)) {
            return $this->_login();
        }

        $this->error = 'Une erreur innattendue est survenue, contactez nous.';
        return false;

    } 

    public function edit($pseudo) {
        global $sql;

        // already ok
        if ($pseudo == $this->_pseudo) return true;

        $backup = $this->_pseudo;
        $this->_pseudo = htmlspecialchars($pseudo);

        // check pseudo
        if (!$this->_checkPseudo()) {
            $this->error = "Votre pseudo doit faire plus de 3 caract&egrave;res.";
            return false;
        }

        if (!$this->_checkPseudoIsFree()) {
            $this->error = "Ce pseudo est d&eacute;j&agrave; prit.";
            return false;
        }

        $query = "UPDATE users SET pseudo = '" . $sql->escape($this->_pseudo) . "' WHERE pseudo = '" . $backup . "'";
        if ($sql->execute($query)) {

             $query = "UPDATE uploads SET user = '" . $sql->escape($this->_pseudo) . "' WHERE user = '" . $backup . "'";
             $sql->execute($query);

             $_SESSION['pseudo'] = $this->_pseudo;
             return true;
        }

        // restore
        $this->_pseudo = $backup;
        $this->error = 'Une erreur innattendue est survenue, contactez nous.';
        return false;
    } 

    public function delete($withImage) {
        global $sql;

        if ($withImage) {
            require_once CLASSES . 'Image.php';
            $images = Image::getFromAuthor($this->_pseudo);
            foreach ($images as $image) {
                $image->delete();
            }
        }

        $query = "DELETE FROM users WHERE pseudo = '" . $this->_pseudo . "'";
        return $sql->execute($query);
    }

    public function changePassword($current, $new, $confirm) {
        global $sql;

        $this->_password = $current;
        $this->_encryptPassword();

        if (!$this->_checkLogin()) {
            $this->error = "Le mot de passe saisi est erron&eacute;";
            return false;
        }

        if ($new != $confirm) {
            $this->error = "Les deux mots de passe sont diff&eacute;rent";
            return false;
        }

        $this->_password = $new;
        if (!$this->_checkPassword()) {
            $this->error = "Votre nouveau mot de passe doit faire plus de 5 caract&egrave;res.";
            return false;
        }

        $this->_encryptPassword();

        $query = "UPDATE users SET password = '" . $sql->escape($this->_password) . "' WHERE pseudo = '" . $this->_pseudo . "'";
        if ($sql->execute($query)) {
            return true;
        }

        return false;
    }

    private function _encryptPassword() {
        $this->_password = crypt($this->_password, $this->_password);
    }

    private function _checkPseudoIsFree() {
        global $sql;

        $query = "SELECT COUNT(id) AS count FROM users WHERE pseudo = '" . $sql->escape($this->_pseudo) . "'";
        $sql->execute($query);
        $row = $sql->next();
        return $row['count'] == 0;
    }

    private function _checkPseudo() {
        return strlen($this->_pseudo) >= 3;
    }

    private function _checkPassword() {
        return strlen($this->_password) >= 5;
    }

    public static function getCount() {
        global $sql;

        $query = "SELECT COUNT(id) AS count FROM users";
        $sql->execute($query);
        $row = $sql->next();

        return $row['count'];
    }

}
