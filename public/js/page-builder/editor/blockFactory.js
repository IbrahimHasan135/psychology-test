import { uid } from './id.js';

const imageA = 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1200&q=80';
const imageB = 'https://images.unsplash.com/photo-1518005020951-eccb494ad742?auto=format&fit=crop&w=1200&q=80';
const imageC = 'https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&w=1200&q=80';
const imageD = 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=1200&q=80';

export function createInitialHeroBlock() {
  const serverDefinition = window.NOVABASE_BLOCK_DEFINITIONS?.hero;
  if (serverDefinition) {
    return {
      id: uid('block'),
      navEnabled: true,
      navLabel: 'Home',
      type: 'hero',
      data: structuredClone(serverDefinition.initial || serverDefinition.defaults),
    };
  }

  return {
    id: uid('block'),
    navEnabled: true,
    navLabel: 'Home',
    type: 'hero',
    data: {
      kicker: 'Template 1',
      title: 'Bangun halaman bisnis dari preview yang langsung bisa diedit',
      text: 'Klik tulisan di halaman untuk mengubah copy. Tambah section dari panel kiri, lalu atur gambar dan layout dari inspector.',
      buttonLabel: 'Mulai sekarang',
      buttonUrl: 'https://example.com',
      image: imageA,
    },
  };
}

export function createBlock(type) {
  const id = uid('block');
  const serverDefinition = window.NOVABASE_BLOCK_DEFINITIONS?.[type];
  if (serverDefinition) {
    return {
      id,
      navEnabled: false,
      navLabel: serverDefinition.label,
      type,
      data: structuredClone(serverDefinition.defaults),
    };
  }

  if (type === 'hero') {
    return {
      id,
      navEnabled: false,
      navLabel: 'Hero',
      type,
      data: {
        kicker: 'Section baru',
        title: 'Judul hero siap diedit langsung',
        text: 'Tulis pesan utama brand atau produk di sini.',
        buttonLabel: 'Call to action',
        buttonUrl: 'https://example.com',
        image: imageB,
      },
    };
  }

  if (type === 'cards') {
    return {
      id,
      navEnabled: false,
      navLabel: 'Fitur',
      type,
      data: {
        title: 'Fitur unggulan',
        text: 'Susun beberapa poin penting dalam format kartu yang mudah dibaca.',
        items: [
          { icon: 'bi-lightning-charge', title: 'Cepat', text: 'Konten bisa diperbarui dari canvas.', link: 'https://example.com/cepat' },
          { icon: 'bi-palette', title: 'Fleksibel', text: 'Cocok untuk banyak template website.', link: 'https://example.com/fleksibel' },
          { icon: 'bi-shield-check', title: 'Rapi', text: 'State tersimpan sebagai JSON sederhana.', link: 'https://example.com/rapi' },
        ],
      },
    };
  }

  if (type === 'split') {
    return {
      id,
      navEnabled: false,
      navLabel: 'Tentang',
      type,
      data: {
        title: 'Ceritakan nilai produk dengan visual yang kuat',
        text: 'Section ini cocok untuk profil usaha, layanan, atau highlight produk dengan satu gambar besar.',
        buttonLabel: 'Pelajari detail',
        buttonUrl: 'https://example.com/detail',
        image: imageC,
        imagePosition: 'left',
      },
    };
  }

  if (type === 'gallery') {
    return {
      id,
      navEnabled: false,
      navLabel: 'Gallery',
      type,
      data: {
        title: 'Gallery project',
        text: 'Tambahkan gambar dari URL atau upload file lokal untuk kebutuhan preview POC.',
        images: [imageA, imageC, imageD, imageB],
      },
    };
  }

  if (type === 'video') {
    return {
      id,
      navEnabled: false,
      navLabel: 'Video',
      type,
      data: {
        title: 'Tampilkan video YouTube',
        text: 'Masukkan link YouTube dari inspector untuk menampilkan video profil, demo produk, atau konten edukasi.',
        videoUrl: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
      },
    };
  }

  if (type === 'logos') {
    return {
      id,
      navEnabled: false,
      navLabel: 'Logo',
      type,
      data: {
        title: 'Dipercaya oleh partner pilihan',
        text: 'Logo dapat diarahkan ke website partner atau halaman portofolio.',
        logos: [
          { name: 'Acme', image: 'https://dummyimage.com/240x100/f8fafc/111827&text=Acme', link: 'https://example.com/acme' },
          { name: 'Northstar', image: 'https://dummyimage.com/240x100/f8fafc/111827&text=Northstar', link: 'https://example.com/northstar' },
          { name: 'Orbit', image: 'https://dummyimage.com/240x100/f8fafc/111827&text=Orbit', link: 'https://example.com/orbit' },
          { name: 'Lumina', image: 'https://dummyimage.com/240x100/f8fafc/111827&text=Lumina', link: 'https://example.com/lumina' },
        ],
      },
    };
  }

  if (type === 'testimonials') {
    return {
      id,
      navEnabled: false,
      navLabel: 'Testimonial',
      type,
      data: {
        title: 'Apa kata pelanggan',
        text: 'Gunakan section ini untuk menampilkan review, studi kasus singkat, atau kutipan client.',
        items: [
          { name: 'Alya Rahman', role: 'Founder', quote: 'Website kami jadi lebih mudah dikelola tanpa menunggu developer untuk perubahan kecil.', link: 'https://example.com/case-study-1' },
          { name: 'Bima Putra', role: 'Marketing Lead', quote: 'Preview langsung membantu tim konten mengambil keputusan visual lebih cepat.', link: 'https://example.com/case-study-2' },
        ],
      },
    };
  }

  if (type === 'pricing') {
    return {
      id,
      navEnabled: false,
      navLabel: 'Harga',
      type,
      data: {
        title: 'Paket layanan',
        text: 'Cocok untuk landing page, katalog, membership, atau layanan berlangganan.',
        plans: [
          { name: 'Starter', price: 'Rp499rb', text: 'Untuk validasi awal.', buttonLabel: 'Pilih Starter', link: 'https://example.com/starter' },
          { name: 'Growth', price: 'Rp1,5jt', text: 'Untuk bisnis yang mulai scale.', buttonLabel: 'Pilih Growth', link: 'https://example.com/growth' },
          { name: 'Custom', price: 'Custom', text: 'Untuk kebutuhan integrasi khusus.', buttonLabel: 'Diskusi Dulu', link: 'https://example.com/custom' },
        ],
      },
    };
  }

  return {
    id,
    navEnabled: false,
    navLabel: 'CTA',
    type: 'cta',
    data: {
      title: 'Siap membuat halaman berikutnya?',
      text: 'Gunakan section ini untuk mengarahkan pengunjung ke aksi utama.',
      buttonLabel: 'Hubungi kami',
      buttonUrl: 'https://example.com/contact',
    },
  };
}
