function chargerPage(fichier) {
  const target = document.getElementById("contenu");

  if (!target) {
    console.error("⚠️ Div #contenu introuvable !");
    return;
  }

  const [fichierBase, params] = fichier.split("?");
  fetch(fichierBase)
    .then(res => res.text())
    .then(html => {
      target.innerHTML = html;

      if (fichierBase === "listClients.html") {
        afficherClients();
      }

      if (fichierBase === "ficheClient.html" && params) {
        const urlParams = new URLSearchParams(params);
        const clientId = urlParams.get("id");
        if (clientId) {
          chargerFicheClient(clientId);
        }
      }
    })
    .catch(err => {
      target.innerHTML = "<p>Erreur lors du chargement de la page.</p>";
      console.error(err);
    });
}


function afficherClients() {
  fetch("/api/client-api.php")
    .then(res => res.json())
    .then(clients => {
      if (!Array.isArray(clients)) {
        console.error("❌ Ce n’est pas une liste de clients :", clients);
        return;
      }

      const listClient = document.getElementById("listClient");
      if (!listClient) {
        console.error("⚠️ #listClient introuvable dans le DOM");
        return;
      }

      listClient.innerHTML = "";

      clients.forEach(client => {
        const div = document.createElement("div");
        div.className = "client-card";
        div.innerHTML = `
          <strong>${client.prenom} ${client.nom}</strong> - ${client.telephone}
          <br>
          <button onclick="chargerPage('ficheClient.html?id=${client.id}')">Voir la fiche</button>
        `;
        listClient.appendChild(div);
      });
    })
    .catch(err => {
      console.error("Erreur lors de l'affichage des clients :", err);
      document.getElementById("listClient").innerHTML = "<p>Erreur de chargement</p>";
    });
}


function chargerFicheClient(id) {
  fetch(`/api/client-api.php?id=${id}`)
    .then(res => res.json())
    .then(client => {
      const zone = document.getElementById("ficheClient");
      if (!zone) {
        console.error("⚠️ #ficheClient introuvable dans le DOM");
        return;
      }

      if (!client || client.error) {
        zone.innerHTML = `<p>Client introuvable.</p>`;
        return;
      }

zone.innerHTML = `
  <h2>${client.prenom} ${client.nom}</h2>
  <p>Téléphone : ${client.telephone}</p>
  <p>Date d'ajout : ${client.date_creation}</p>
  <br>
  <button onclick="supprimerClient(${client.id})" style="color:red;">🗑 Supprimer</button>
  <br><br>
  <button onclick="chargerPage('clients.html')">⬅ Retour</button>
`;

    })
    .catch(err => {
      const zone = document.getElementById("ficheClient");
      if (zone) zone.innerHTML = "<p>Erreur de chargement de la fiche client</p>";
      console.error("Erreur JS fiche client :", err);
    });
}

function supprimerClient(id) {
  console.log("ID à supprimer :", id); // ← vérification JS

  if (!confirm("Voulez-vous vraiment supprimer ce client ?")) return;

  fetch(`/api/supprimer-client.php?id=${id}`, {
    method: "DELETE"
  })
    .then(res => res.json())
    .then(data => {
      alert(data.message);
      if (data.success) {
        chargerPage("clients.html");
      }
    })
    .catch(err => {
      alert("Erreur API suppression.");
      console.error("Erreur API suppression :", err);
    });
}
