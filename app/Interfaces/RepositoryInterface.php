<?php

namespace App\Interfaces;

interface RepositoryInterface 
{
    public function create(array $data);
    public function update(array $data, $id);
    public function find($id);
    public function delete($id);
}