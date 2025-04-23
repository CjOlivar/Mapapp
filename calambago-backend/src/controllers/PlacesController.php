<?php

namespace src\Controllers;

use src\Models\Place;
use src\Helpers\Response;
use PDO;

class PlacesController
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function createPlace($data)
    {
        // Logic to create a new place
        $place = new Place($this->db);
        $result = $place->create($data);

        if ($result) {
            Response::sendSuccess("Place created successfully.", $result);
        } else {
            Response::sendError("Failed to create place.");
        }
    }

    public function getPlaces()
    {
        // Logic to retrieve all places
        $place = new Place($this->db);
        $places = $place->getAll();

        Response::sendSuccess("Places retrieved successfully.", $places);
    }

    public function updatePlace($id, $data)
    {
        // Logic to update an existing place
        $place = new Place($this->db);
        $result = $place->update($id, $data);

        if ($result) {
            Response::sendSuccess("Place updated successfully.");
        } else {
            Response::sendError("Failed to update place.");
        }
    }

    public function deletePlace($id)
    {
        // Logic to delete a place
        $place = new Place($this->db);
        $result = $place->delete($id);

        if ($result) {
            Response::sendSuccess("Place deleted successfully.");
        } else {
            Response::sendError("Failed to delete place.");
        }
    }

    public function getPlace($id)
    {
        $place = new Place($this->db);
        $result = $place->find($id);

        if ($result) {
            Response::sendSuccess("Place retrieved successfully.", $result);
        } else {
            Response::sendError("Place not found.");
        }
    }
}