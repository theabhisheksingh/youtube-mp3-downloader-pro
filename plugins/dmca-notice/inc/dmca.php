<?php

namespace uniqueX;

class DMCA
{
    public static function addDMCA($type, $value, $note = null)
    {
        $return = false;
        if (is_string($type) && is_string($value) && (is_string($note) || $note == null)) {
            // Pack record
            $record = json_encode(array($type, $value, time(), $note));
            // Update DMCA records
            if (is_resource($dmca = @fopen(self::dmcaFile(), 'a+'))) {
                fwrite($dmca, "{$record}\n");
                // Close DMCA file
                fclose($dmca);
                // DONE
                $return = true;
            }
        }
        return $return;
    }

    public static function getDMCA()
    {
        $return = array();
        if (is_file(self::dmcaFile())) {
            $records = explode("\n", file_get_contents(self::dmcaFile()));
            foreach ($records as $pos => $data) {
                if (strlen(trim($data)) > 0) {
                    $records[$pos] = json_decode($data);
                } else {
                    unset($records[$pos]);
                }
            }
            $return = array_values($records);
        }
        return $return;
    }

    public static function delDMCA($value)
    {
        if (is_string($value) && strlen(trim($value)) > 0) {
            // Get current DMCA records
            $records = self::getDMCA();
            $del = false;
            foreach ($records as $pos => $record) {
                if ($record[1] == $value) {
                    unset($records[$pos]);
                    $del = true;
                    break;
                }
            }
            // Update records
            file_put_contents(self::dmcaFile(), '');
            if ($del && is_resource($dmca = @fopen(self::dmcaFile(), 'w'))) {
                foreach ($records as $record) {
                    fwrite($dmca, json_encode($record) . "\n");
                }
                fclose($dmca);
            }
        }
    }

    private static function dmcaFile()
    {
        return __DIR__ . '/../../../store/dmca.txt';
    }
}
