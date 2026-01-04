function remplirCarte() {
    var select = document.getElementById('id_carte');
    var option = select.options[select.selectedIndex];

    if(option.value !== "") {
        document.getElementById('numero_carte').value = option.dataset.num;
        document.getElementById('date_expiration').value = option.dataset.date;
        document.getElementById('cvv').value = option.dataset.cvv;
        document.getElementById('nom_titulaire').value = option.dataset.nom;
    } else {
        document.getElementById('numero_carte').value = '';
        document.getElementById('date_expiration').value = '';
        document.getElementById('cvv').value = '';
        document.getElementById('nom_titulaire').value = '';
    }
}