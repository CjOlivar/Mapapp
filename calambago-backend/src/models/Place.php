<?php

namespace src\Models;

class Place {
    private static $places = [];
    private static $nextId = 1;

    public function create($data) {
        $data['id'] = self::$nextId++;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = $data['created_at'];
        self::$places[] = $data;
        return $data;
    }

    public function getAll() {
        return array_values(self::$places);
    }

    public function update($id, $data) {
        foreach (self::$places as &$place) {
            if ($place['id'] == $id) {
                $place = array_merge($place, $data);
                $place['updated_at'] = date('Y-m-d H:i:s');
                return $place;
            }
        }
        return false;
    }

    public function delete($id) {
        foreach (self::$places as $i => $place) {
            if ($place['id'] == $id) {
                array_splice(self::$places, $i, 1);
                return true;
            }
        }
        return false;
    }

    public function find($id) {
        foreach (self::$places as $place) {
            if ($place['id'] == $id) {
                return $place;
            }
        }
        return false;
    }
}