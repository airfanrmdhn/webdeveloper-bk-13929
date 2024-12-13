<?php

namespace App\Controllers;

use App\Models\DokterModel;
use App\Models\PoliModel;
use App\Models\UsersModel;

class Dokter extends BaseController
{
  protected $dokterModel;
  protected $poliModel;
  protected $userModel;

  public function __construct()
  {
    $this->dokterModel = new DokterModel();
    $this->poliModel = new PoliModel();
    $this->userModel = new UsersModel();
    helper(['form', 'url']); // Load helpers here to make them available in all methods
  }

  public function index()
  {
    $dokterData = $this->dokterModel
      ->select('*, dokter.id as id')
      ->join('poli', 'dokter.id_poli = poli.id')
      ->findAll(); // Fetch all records from the model

    $data = [
      'dokter' => $dokterData
    ];
    return view('dokter/dokter', $data);
  }

  public function create()
  {
    $poliData = $this->poliModel->findAll();

    $data = [
      'poli' => $poliData,
    ];

    return view('dokter/create', $data);
  }

  /**
   * Store function
   */
  public function store()
  {
    // Define validation
    $validation = $this->validate([
      'nama' => [
        'rules'  => 'required',
        'errors' => [
          'required' => 'Masukkan Nama Dokter.'
        ]
      ],
      'alamat' => [],
      'no_hp' => [
        'rules'  => 'required',
        'errors' => [
          'required' => 'Masukkan No HP Dokter.'
        ]
      ],
      'id_poli' => [
        'rules'  => 'required',
        'errors' => [
          'required' => 'Silahkan Pilih Poli.'
        ]
      ],
    ]);

    if (!$validation) {
      // Render view with error validation message
      $poliData = $this->poliModel->findAll();
      return view('dokter/create', [
        'poli' => $poliData,
        'validation' => $this->validator
      ]);
    } else {
      // Insert data into database
      $this->dokterModel->insert([
        'nama' => $this->request->getPost('nama'),
        'alamat' => $this->request->getPost('alamat'),
        'no_hp' => $this->request->getPost('no_hp'),
        'id_poli' => $this->request->getPost('id_poli'),
      ]);

      // Get last inserted ID from dokter table
      $dokterId = $this->dokterModel->getInsertID();

      // Prepare username by removing spaces and converting to lowercase
      $username = strtolower(str_replace(' ', '', $this->request->getPost('nama')));

      // Insert data into users table
      $this->userModel->insert([
        'username' => $username,
        'password' => password_hash('dokter123', PASSWORD_BCRYPT), // Use proper password hashing
        'role' => 'dokter',
        'dokter_id' => $dokterId
      ]);

      // Flash message
      session()->setFlashdata('message', 'Dokter Berhasil Ditambahkan');

      return redirect()->to(base_url('dokter'));
    }
  }

  public function edit($id)
  {
    $dokter = $this->dokterModel->find($id);
    $poliData = $this->poliModel->findAll();

    if (!$dokter) {
      // Handle case where the dokter is not found
      session()->setFlashdata('message', 'Data tidak ditemukan');
      return redirect()->to(base_url('dokter'));
    }

    $data = [
      'id' => $dokter['id'],
      'nama' => $dokter['nama'],
      'alamat' => $dokter['alamat'],
      'no_hp' => $dokter['no_hp'],
      'id_poli' => $dokter['id_poli'],
      'poli' => $poliData,
    ];

    return view('dokter/edit', $data);
  }

  /**
   * Update function
   */
  public function update($id)
  {
    // Get the current record to check its existing 'tanggal' value
    $currentData = $this->dokterModel->find($id);

    // Define validation rules, allowing the same 'tanggal' if it hasn't changed
    $validation = $this->validate([
      'nama' => [
        'rules'  => 'required',
        'errors' => [
          'required' => 'Masukkan Nama Dokter.'
        ]
      ],
      'alamat' => [],
      'no_hp' => [
        'rules'  => 'required',
        'errors' => [
          'required' => 'Masukkan No HP Dokter.'
        ]
      ],
      'id_poli' => [
        'rules'  => 'required',
        'errors' => [
          'required' => 'Silahkan Pilih Poli.'
        ]
      ],
    ]);

    if (!$validation) {
      $poliData = $this->poliModel->findAll();
      // Pass the current data and validation errors to the view
      return view('dokter/edit', [
        'id' => $currentData['id'],
        'nama' => $currentData['nama'],
        'alamat' => $currentData['alamat'],
        'no_hp' => $currentData['no_hp'],
        'id_poli' => $currentData['id_poli'],
        'poli' => $poliData,
        'validation' => $this->validator
      ]);
    } else {
      // Update data in the database
      $this->dokterModel->update($id, [
        'nama' => $this->request->getPost('nama'),
        'alamat' => $this->request->getPost('alamat'),
        'no_hp' => $this->request->getPost('no_hp'),
        'id_poli' => $this->request->getPost('id_poli'),
      ]);

      // Flash message
      session()->setFlashdata('message', 'Dokter Berhasil Diupdate');
      return redirect()->to(base_url('dokter'));
    }
  }

  /**
   * Delete function
   */
  public function delete($id)
  {
    $dokter = $this->dokterModel->find($id);

    if ($dokter) {
      $this->dokterModel->delete($id);
      $this->userModel->where('dokter_id', $id)->delete();

      // Flash message
      session()->setFlashdata('message', 'Dokter Berhasil Dihapus');

      return redirect()->to(base_url('dokter'));
    } else {
      // Flash message
      session()->setFlashdata('message', 'Data tidak ditemukan');
      return redirect()->to(base_url('dokter'));
    }
  }
}
