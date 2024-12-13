<?php

namespace App\Controllers;

use App\Models\UsersModel;
use App\Models\PasienModel;

class Pasien extends BaseController
{
  protected $pasienModel;
  protected $userModel;

  public function __construct()
  {
    $this->pasienModel = new PasienModel();
    $this->userModel = new UsersModel();
    helper(['form', 'url']); // Load helpers here to make them available in all methods
  }

  public function index()
  {
    $pasienData = $this->pasienModel->findAll(); // Fetch all records from the model

    $data = [
      'pasien' => $pasienData
    ];
    return view('pasien/pasien', $data);
  }

  public function create()
  {
    return view('pasien/create');
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
          'required' => 'Masukkan Nama Pasien.'
        ]
      ],
      'alamat' => [
        'rules'  => 'required',
        'errors' => [
          'required' => 'Masukkan Alamat Pasien.'
        ]
      ],
      'no_ktp' => [
        'rules'  => 'required|is_unique[pasien.no_ktp]',
        'errors' => [
          'required' => 'Masukkan No KTP Pasien.',
          'is_unique' => 'No KTP Pasien sudah ada.'
        ]
      ],
      'no_hp' => [
        'rules'  => 'required',
        'errors' => [
          'required' => 'Masukkan No HP Pasien.'
        ]
      ],
      'no_rm' => [],
    ]);

    if (!$validation) {
      // Render view with error validation message
      return view('pasien/create', [
        'validation' => $this->validator
      ]);
    } else {
      // Insert data into database
      $this->pasienModel->insert([
        'nama' => $this->request->getPost('nama'),
        'alamat' => $this->request->getPost('alamat'),
        'no_ktp' => $this->request->getPost('no_ktp'),
        'no_hp' => $this->request->getPost('no_hp'),
        'no_rm' => $this->request->getPost('no_rm'),
      ]);

      // Get last inserted ID from dokter table
      $pasienId = $this->pasienModel->getInsertID();

      // Prepare username by removing spaces and converting to lowercase
      $username = strtolower(str_replace(' ', '', $this->request->getPost('nama')));

      // Insert data into users table
      $this->userModel->insert([
        'username' => $username,
        'password' => password_hash('pasien123', PASSWORD_BCRYPT), // Use proper password hashing
        'role' => 'pasien',
        'pasien_id' => $pasienId
      ]);

      // Flash message
      session()->setFlashdata('message', 'Pasien Berhasil Ditambahkan');

      return redirect()->to(base_url('pasien'));
    }
  }

  public function edit($id)
  {
    $pasien = $this->pasienModel->find($id);

    if (!$pasien) {
      // Handle case where the pasien is not found
      session()->setFlashdata('message', 'Data tidak ditemukan');
      return redirect()->to(base_url('pasien'));
    }

    $data = [
      'id' => $pasien['id'],
      'nama' => $pasien['nama'],
      'alamat' => $pasien['alamat'],
      'no_ktp' => $pasien['no_ktp'],
      'no_hp' => $pasien['no_hp'],
      'no_rm' => $pasien['no_rm'],
    ];

    return view('pasien/edit', $data);
  }

  /**
   * Update function
   */
  public function update($id)
  {
    // Get the current record to check its existing 'tanggal' value
    $currentData = $this->pasienModel->find($id);

    // Define validation rules, allowing the same 'tanggal' if it hasn't changed
    $validation = $this->validate([
      'nama' => [
        'rules'  => 'required',
        'errors' => [
          'required' => 'Masukkan Nama Pasien.'
        ]
      ],
      'alamat' => [
        'rules'  => 'required',
        'errors' => [
          'required' => 'Masukkan Alamat Pasien.'
        ]
      ],
      'no_ktp' => [
        'rules'  => 'required|is_unique[pasien.no_ktp,id,' . $id . ']',
        'errors' => [
          'required' => 'Masukkan No KTP Pasien.',
          'is_unique' => 'No KTP Pasien sudah ada.'
        ]
      ],
      'no_hp' => [
        'rules'  => 'required',
        'errors' => [
          'required' => 'Masukkan No HP Pasien.'
        ]
      ],
      'no_rm' => [],
    ]);

    if (!$validation) {
      // Pass the current data and validation errors to the view
      return view('pasien/edit', [
        'id' => $currentData['id'],
        'nama' => $currentData['nama'],
        'alamat' => $currentData['alamat'],
        'no_ktp' => $currentData['no_ktp'],
        'no_hp' => $currentData['no_hp'],
        'no_rm' => $currentData['no_rm'],
        'validation' => $this->validator
      ]);
    } else {
      // Update data in the database
      $this->pasienModel->update($id, [
        'nama' => $this->request->getPost('nama'),
        'alamat' => $this->request->getPost('alamat'),
        'no_ktp' => $this->request->getPost('no_ktp'),
        'no_hp' => $this->request->getPost('no_hp'),
        'no_rm' => $this->request->getPost('no_rm'),
      ]);

      // Flash message
      session()->setFlashdata('message', 'Pasien Berhasil Diupdate');
      return redirect()->to(base_url('pasien'));
    }
  }

  /**
   * Delete function
   */
  public function delete($id)
  {
    $pasien = $this->pasienModel->find($id);

    if ($pasien) {
      $this->pasienModel->delete($id);
      $this->userModel->where('pasien_id', $id)->delete();

      // Flash message
      session()->setFlashdata('message', 'Pasien Berhasil Dihapus');

      return redirect()->to(base_url('pasien'));
    } else {
      // Flash message
      session()->setFlashdata('message', 'Data tidak ditemukan');
      return redirect()->to(base_url('pasien'));
    }
  }

  public function daftar()
  {
    return view('pasien/daftar');
  }

  public function prosesDaftar()
  {
    // Define validation
    $validation = $this->validate([
      'nama' => [
        'rules'  => 'required',
        'errors' => [
          'required' => 'Masukkan Nama Pasien.'
        ]
      ],
      'alamat' => [
        'rules'  => 'required',
        'errors' => [
          'required' => 'Masukkan Alamat Pasien.'
        ]
      ],
      'no_ktp' => [
        'rules'  => 'required|is_unique[pasien.no_ktp]',
        'errors' => [
          'required' => 'Masukkan No KTP Pasien.',
          'is_unique' => 'No KTP Pasien sudah ada.'
        ]
      ],
      'no_hp' => [
        'rules'  => 'required',
        'errors' => [
          'required' => 'Masukkan No HP Pasien.'
        ]
      ],
    ]);

    if (!$validation) {
      // Render view with error validation message
      return view('pasien/daftar', [
        'validation' => $this->validator
      ]);
    } else {
      // Insert data into database
      $this->pasienModel->insert([
        'nama' => $this->request->getPost('nama'),
        'alamat' => $this->request->getPost('alamat'),
        'no_ktp' => $this->request->getPost('no_ktp'),
        'no_hp' => $this->request->getPost('no_hp'),
      ]);

      // Get last inserted ID from pasien table
      $pasienId = $this->pasienModel->getInsertID();

      // Generate no_rm
      $no_rm = 'RM' . str_pad($pasienId, 5, '0',
        STR_PAD_LEFT
      );

      // Update no_rm di tabel pasien
      $this->pasienModel->update($pasienId, [
        'no_rm' => $no_rm
      ]);

      // Prepare username by removing spaces and converting to lowercase
      $username = strtolower(str_replace(' ', '', $this->request->getPost('nama')));

      // Insert data into users table
      $this->userModel->insert([
        'username' => $username,
        'password' => password_hash('pasien123', PASSWORD_BCRYPT), // Use proper password hashing
        'role' => 'pasien',
        'pasien_id' => $pasienId
      ]);

      // Flash message
      session()->setFlashdata('message', 'Pasien Berhasil Ditambahkan');

      $data = [
        'nama' => $this->request->getPost('nama'),
        'alamat' => $this->request->getPost('alamat'),
        'no_ktp' => $this->request->getPost('no_ktp'),
        'no_hp' => $this->request->getPost('no_hp'),
        'username' => $username,
        'password' => 'pasien123',
        'no_rm' => $no_rm,
      ];

      return view('pasien/landing', $data);
    }
  }
}
