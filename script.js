// Nail Booking Website - Main JavaScript File
// Current Date and Time: 2025-12-15 11:19:46 UTC

// ===== DOM Elements =====
document.addEventListener('DOMContentLoaded', function() {
  initializeWebsite();
});

function initializeWebsite() {
  setupEventListeners();
  loadBookings();
  updateDateTime();
}

// ===== Date and Time Functionality =====
function getCurrentDateTime() {
  const now = new Date();
  const year = now.getUTCFullYear();
  const month = String(now.getUTCMonth() + 1).padStart(2, '0');
  const day = String(now.getUTCDate()).padStart(2, '0');
  const hours = String(now.getUTCHours()).padStart(2, '0');
  const minutes = String(now.getUTCMinutes()).padStart(2, '0');
  const seconds = String(now.getUTCSeconds()).padStart(2, '0');
  
  return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}

function updateDateTime() {
  const dateTimeElement = document.getElementById('current-datetime');
  if (dateTimeElement) {
    dateTimeElement.textContent = getCurrentDateTime();
    setInterval(() => {
      dateTimeElement.textContent = getCurrentDateTime();
    }, 1000);
  }
}

// ===== Event Listeners =====
function setupEventListeners() {
  // Booking form submission
  const bookingForm = document.getElementById('booking-form');
  if (bookingForm) {
    bookingForm.addEventListener('submit', handleBookingSubmit);
  }

  // Navigation menu
  const navLinks = document.querySelectorAll('nav a');
  navLinks.forEach(link => {
    link.addEventListener('click', handleNavigation);
  });

  // Service selection
  const serviceButtons = document.querySelectorAll('.service-btn');
  serviceButtons.forEach(button => {
    button.addEventListener('click', handleServiceSelection);
  });

  // Time slot selection
  const timeSlots = document.querySelectorAll('.time-slot');
  timeSlots.forEach(slot => {
    slot.addEventListener('click', handleTimeSlotSelection);
  });

  // Modal close buttons
  const closeButtons = document.querySelectorAll('.close-btn');
  closeButtons.forEach(button => {
    button.addEventListener('click', handleModalClose);
  });
}

// ===== Booking Management =====
function handleBookingSubmit(event) {
  event.preventDefault();
  
  const formData = new FormData(event.target);
  const booking = {
    id: generateBookingId(),
    name: formData.get('name'),
    email: formData.get('email'),
    phone: formData.get('phone'),
    service: formData.get('service'),
    date: formData.get('date'),
    time: formData.get('time'),
    notes: formData.get('notes'),
    createdAt: getCurrentDateTime()
  };

  if (validateBooking(booking)) {
    saveBooking(booking);
    showNotification('Booking confirmed successfully!', 'success');
    event.target.reset();
    displayBookings();
  } else {
    showNotification('Please fill in all required fields.', 'error');
  }
}

function validateBooking(booking) {
  return booking.name && booking.email && booking.phone && 
         booking.service && booking.date && booking.time;
}

function generateBookingId() {
  return 'BOOKING_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
}

function saveBooking(booking) {
  let bookings = JSON.parse(localStorage.getItem('bookings')) || [];
  bookings.push(booking);
  localStorage.setItem('bookings', JSON.stringify(bookings));
}

function loadBookings() {
  const bookings = JSON.parse(localStorage.getItem('bookings')) || [];
  return bookings;
}

function displayBookings() {
  const bookingsContainer = document.getElementById('bookings-list');
  if (!bookingsContainer) return;

  const bookings = loadBookings();
  bookingsContainer.innerHTML = '';

  if (bookings.length === 0) {
    bookingsContainer.innerHTML = '<p class="no-bookings">No bookings yet.</p>';
    return;
  }

  bookings.forEach(booking => {
    const bookingElement = createBookingElement(booking);
    bookingsContainer.appendChild(bookingElement);
  });
}

function createBookingElement(booking) {
  const div = document.createElement('div');
  div.className = 'booking-card';
  div.setAttribute('data-booking-id', booking.id);
  
  div.innerHTML = `
    <div class="booking-header">
      <h3>${booking.service}</h3>
      <span class="booking-id">#${booking.id.slice(-6)}</span>
    </div>
    <div class="booking-details">
      <p><strong>Name:</strong> ${escapeHtml(booking.name)}</p>
      <p><strong>Email:</strong> ${escapeHtml(booking.email)}</p>
      <p><strong>Phone:</strong> ${escapeHtml(booking.phone)}</p>
      <p><strong>Date:</strong> ${booking.date}</p>
      <p><strong>Time:</strong> ${booking.time}</p>
      ${booking.notes ? `<p><strong>Notes:</strong> ${escapeHtml(booking.notes)}</p>` : ''}
      <p><strong>Booked on:</strong> ${booking.createdAt}</p>
    </div>
    <div class="booking-actions">
      <button class="btn-edit" onclick="editBooking('${booking.id}')">Edit</button>
      <button class="btn-delete" onclick="deleteBooking('${booking.id}')">Delete</button>
    </div>
  `;
  
  return div;
}

function editBooking(bookingId) {
  const bookings = loadBookings();
  const booking = bookings.find(b => b.id === bookingId);
  
  if (booking) {
    // Pre-fill form with booking data
    const form = document.getElementById('booking-form');
    if (form) {
      form.elements['name'].value = booking.name;
      form.elements['email'].value = booking.email;
      form.elements['phone'].value = booking.phone;
      form.elements['service'].value = booking.service;
      form.elements['date'].value = booking.date;
      form.elements['time'].value = booking.time;
      form.elements['notes'].value = booking.notes;
      
      // Mark for update
      form.setAttribute('data-edit-id', bookingId);
      form.scrollIntoView({ behavior: 'smooth' });
      showNotification('Editing booking. Submit to save changes.', 'info');
    }
  }
}

function deleteBooking(bookingId) {
  if (confirm('Are you sure you want to delete this booking?')) {
    let bookings = JSON.parse(localStorage.getItem('bookings')) || [];
    bookings = bookings.filter(b => b.id !== bookingId);
    localStorage.setItem('bookings', JSON.stringify(bookings));
    displayBookings();
    showNotification('Booking deleted successfully.', 'success');
  }
}

// ===== Service Selection =====
function handleServiceSelection(event) {
  const serviceButtons = document.querySelectorAll('.service-btn');
  serviceButtons.forEach(btn => btn.classList.remove('active'));
  
  event.target.classList.add('active');
  
  const serviceInput = document.getElementById('service');
  if (serviceInput) {
    serviceInput.value = event.target.dataset.service;
  }
}

// ===== Time Slot Selection =====
function handleTimeSlotSelection(event) {
  const timeSlots = document.querySelectorAll('.time-slot');
  timeSlots.forEach(slot => slot.classList.remove('selected'));
  
  event.target.classList.add('selected');
  
  const timeInput = document.getElementById('time');
  if (timeInput) {
    timeInput.value = event.target.textContent;
  }
}

// ===== Navigation =====
function handleNavigation(event) {
  event.preventDefault();
  const target = event.target.getAttribute('href');
  
  const sections = document.querySelectorAll('section');
  sections.forEach(section => section.style.display = 'none');
  
  const targetSection = document.querySelector(target);
  if (targetSection) {
    targetSection.style.display = 'block';
    targetSection.scrollIntoView({ behavior: 'smooth' });
  }
}

// ===== Modal Management =====
function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.add('active');
    modal.style.display = 'block';
  }
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.remove('active');
    modal.style.display = 'none';
  }
}

function handleModalClose(event) {
  const modal = event.target.closest('.modal');
  if (modal) {
    modal.classList.remove('active');
    modal.style.display = 'none';
  }
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
  if (event.target.classList.contains('modal')) {
    event.target.classList.remove('active');
    event.target.style.display = 'none';
  }
});

// ===== Notifications =====
function showNotification(message, type = 'info') {
  const notification = document.createElement('div');
  notification.className = `notification notification-${type}`;
  notification.textContent = message;
  
  document.body.appendChild(notification);
  
  // Trigger animation
  setTimeout(() => notification.classList.add('show'), 10);
  
  // Auto-remove
  setTimeout(() => {
    notification.classList.remove('show');
    setTimeout(() => notification.remove(), 300);
  }, 3000);
}

// ===== Utility Functions =====
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

// ===== Available Time Slots =====
function getAvailableTimeSlots(selectedDate) {
  const slots = [
    '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
    '13:00', '13:30', '14:00', '14:30', '15:00', '15:30',
    '16:00', '16:30', '17:00', '17:30'
  ];
  
  // Filter out booked slots
  const bookings = loadBookings();
  const bookedTimes = bookings
    .filter(b => b.date === selectedDate)
    .map(b => b.time);
  
  return slots.filter(slot => !bookedTimes.includes(slot));
}

// ===== Date Validation =====
function isValidDate(dateString) {
  const regex = /^\d{4}-\d{2}-\d{2}$/;
  if (!regex.test(dateString)) return false;
  
  const date = new Date(dateString);
  return date instanceof Date && !isNaN(date);
}

// ===== Email Validation =====
function isValidEmail(email) {
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return regex.test(email);
}

// ===== Phone Validation =====
function isValidPhone(phone) {
  const regex = /^\d{10,}$/;
  return regex.test(phone.replace(/\D/g, ''));
}

// ===== Search and Filter =====
function filterBookings(query) {
  const bookings = loadBookings();
  const filtered = bookings.filter(booking => {
    const searchText = `${booking.name} ${booking.email} ${booking.phone} ${booking.service}`.toLowerCase();
    return searchText.includes(query.toLowerCase());
  });
  
  return filtered;
}

// ===== Export Bookings =====
function exportBookingsToCSV() {
  const bookings = loadBookings();
  
  if (bookings.length === 0) {
    showNotification('No bookings to export.', 'warning');
    return;
  }
  
  const headers = ['ID', 'Name', 'Email', 'Phone', 'Service', 'Date', 'Time', 'Notes', 'Created At'];
  const rows = bookings.map(b => [
    b.id, b.name, b.email, b.phone, b.service, b.date, b.time, b.notes, b.createdAt
  ]);
  
  let csvContent = headers.join(',') + '\n';
  rows.forEach(row => {
    csvContent += row.map(cell => `"${cell || ''}"`).join(',') + '\n';
  });
  
  const blob = new Blob([csvContent], { type: 'text/csv' });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `bookings_${getCurrentDateTime().replace(/[:\s-]/g, '_')}.csv`;
  a.click();
  window.URL.revokeObjectURL(url);
  
  showNotification('Bookings exported successfully!', 'success');
}

// ===== Initialize on page load =====
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeWebsite);
} else {
  initializeWebsite();
}
