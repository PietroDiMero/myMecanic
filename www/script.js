document.addEventListener("DOMContentLoaded", () => {
  const listClient = document.getElementById("listClient");

  // Appel vers l'API PHP pour récupérer les clients
  fetch("/api/client-api.php")
    .then(res => res.json())
    .then(clients => {
      if (clients.length === 0) {
        listClient.innerHTML = "<p>Aucun client trouvé.</p>";
        return;
      }

      // Affiche chaque client
      clients.forEach(client => {
        const div = document.createElement("div");
        div.className = "client-card"; // pour le style éventuel
        div.textContent = `${client.prenom} ${client.nom} - ${client.telephone}`;
        listClient.appendChild(div);
      });
    })
    .catch(err => {
      listClient.innerHTML = "<p>Erreur lors du chargement des clients.</p>";
      console.error(err);
    });
});
