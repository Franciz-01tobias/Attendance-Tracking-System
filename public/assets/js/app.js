async function updateAttendanceItem(submissionId, itemId, csrf) {
  const status = document.getElementById(`item-status-${itemId}`)?.value;
  const note = document.getElementById(`item-note-${itemId}`)?.value || '';

  const formData = new URLSearchParams();
  formData.set('_method', 'PATCH');
  formData.set('_csrf', csrf);
  formData.set('status', status);
  formData.set('note', note);

  const res = await fetch(`/submissions/${submissionId}/items/${itemId}`, {
    method: 'POST',
    headers: {'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded'},
    body: formData.toString(),
  });
  const data = await res.json();
  if (!data.ok) {
    alert(data.message || 'Update failed');
    return;
  }
  alert('Attendance item updated');
}

const crForm = document.getElementById('cr-submit-form');
if (crForm) {
  crForm.addEventListener('submit', (e) => {
    const rows = Array.from(document.querySelectorAll('#cr-attendance-table .status-select'));
    const items = rows.map((el) => ({
      student_id: Number(el.getAttribute('data-student-id')),
      status: el.value,
      note: null,
    }));

    document.getElementById('items_json').value = JSON.stringify(items);
  });
}
