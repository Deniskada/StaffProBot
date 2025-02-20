function startShift() {
    if (confirm('Начать новую смену?')) {
        fetch('/shifts/start', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.error);
            }
        });
    }
}

function endShift(shiftId) {
    if (confirm('Завершить текущую смену?')) {
        fetch(`/shifts/${shiftId}/end`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.error);
            }
        });
    }
}

function filterShifts(period) {
    const url = new URL(window.location);
    url.searchParams.set('period', period);
    window.location = url;
} 