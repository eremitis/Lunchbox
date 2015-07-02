<?php

class LunchboxException extends Exception
{
    private $mode;
    private $lunchbox_pdo;
    private $insert_sql = 'INSERT INTO exceptions VALUES(null, :message, :code, :file, :line, null)';
    private $error_file = '..' . DIRECTORY_SEPARATOR . 'lunchbox_errors.csv';

    public function __construct($message, $code = 0, $mode = 'PDO', $error_file = ('..' . DIRECTORY_SEPARATOR . 'lunchbox_errors.csv')) {
        $this->message = $message;
        $this->code = $code;
        $this->error_file = $error_file;

        if(\strcasecmp($this->mode, 'pdo') === 0) {
            try {
                $this->lunchbox_pdo = new LunchboxPDO();
                $insert_statement = $this->lunchbox_pdo->prepare($this->insert_sql);

                if(!$insert_statement->execute(array(':message' => $this->message,
                                                 ':code' => $this->code,
                                                 ':file' => $this->file,
                                                 ':line' => $this->line)))
                    throw new LunchboxException($this->message, $this->code, 'FILE');
            } catch (PDOException $error) {
                throw new LunchboxException($this->message, $this->code, 'FILE');
            }
        } elseif (\strcasecmp($this->mode, 'file') === 0) {
            $log_file = \fopen($this->error_file, 'a');
            if($log_file) {
                \fputcsv($log_file, array(\date(DATE_ISO8601), $this->message, $this->code, $this->file, $this->line));
            } else {
                throw new Exception('Nie udało się otworzyć pliku błędów');
            }
        } else {
            throw new Exception('Nieprawidłowe wywołanie klasy LunchboxException');
        }
    }
}

?>