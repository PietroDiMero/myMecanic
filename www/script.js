function chargerPage(fichier) {
  const target = document.getElementById("contenu"); // 🔍 Récupère la div où le contenu HTML va être injecté

  if (!target) {
    console.error("⚠️ Div #contenu introuvable !");
    return; // 🛑 Stoppe si la div n’existe pas
  }

  const [fichierBase, params] = fichier.split("?"); // ✂️ Sépare le nom de fichier et les paramètres d’URL
  fetch(fichierBase) // 🔄 Charge le fichier HTML via fetch
    .then(res => res.text()) // 🔃 Convertit la réponse en texte (HTML)
    .then(html => {
      target.innerHTML = html; // 🧩 Injecte le HTML dans la div cible

      // 👇 Appelle la fonction selon la page chargée
      if (fichierBase === "listClients.html") {
        afficherClients();
      }

      if (fichierBase === "ficheClient.html" && params) {
        const urlParams = new URLSearchParams(params);
        const clientId = urlParams.get("id");
        if (clientId) {
          chargerFicheClient(clientId); // 🔍 Charge les infos du client
        }
      }

      if (fichierBase === "vehicules.html") {
        const urlParams = new URLSearchParams(params);
        const clientId = urlParams.get("client");

        afficherVehiculesClient(clientId); // 🚗 Liste les véhicules du client
        initVehiculeForm(clientId);        // 📥 Injecte l’id client dans le formulaire
      }
    })
    .catch(err => {
      target.innerHTML = "<p>Erreur lors du chargement de la page.</p>"; // ❌ Affiche une erreur
      console.error(err);
    });
}



function afficherClients() {
  fetch("/api/client-api.php") // 🔄 Appel à l'API pour récupérer tous les clients
    .then(res => res.json()) // 📦 Convertit la réponse en JSON
    .then(clients => {
      if (!Array.isArray(clients)) {
        console.error("❌ Ce n’est pas une liste de clients :", clients);
        return;
      }

      const listClient = document.getElementById("listClient"); // 📍 Récupère la div qui contiendra la liste

      if (!listClient) {
        console.error("⚠️ #listClient introuvable dans le DOM");
        return;
      }

      listClient.innerHTML = ""; // 🔄 Vide la liste existante

      clients.forEach(client => {
        const div = document.createElement("div"); // 📦 Crée une carte pour chaque client
        div.className = "client-card";
        div.innerHTML = `
          <strong>${client.prenom} ${client.nom}</strong> - ${client.telephone}
          <br>
          <button onclick="chargerPage('ficheClient.html?id=${client.id}')">Voir la fiche</button>
        `;
        listClient.appendChild(div); // 📌 Ajoute à la liste
      });
    })
    .catch(err => {
      console.error("Erreur lors de l'affichage des clients :", err);
      document.getElementById("listClient").innerHTML = "<p>Erreur de chargement</p>";
    });
}


function chargerFicheClient(id) {
  fetch(`/api/client-api.php?id=${id}`) // 🔄 Récupère les infos d’un client précis
    .then(res => res.json())
    .then(client => {
      const zone = document.getElementById("ficheClient"); // 📍 Zone d'affichage

      if (!zone) {
        console.error("⚠️ #ficheClient introuvable dans le DOM");
        return;
      }

      if (!client || client.error) {
        zone.innerHTML = `<p>Client introuvable.</p>`;
        return;
      }

      // 🧾 Affiche les données du client
      zone.innerHTML = `
        <h2>${client.prenom} ${client.nom}</h2>
        <p>Téléphone : ${client.telephone}</p>
        <p>Date d'ajout : ${client.date_creation}</p>
        <br>
        <button onclick="chargerPage('vehicules.html?client=${client.id}')">Véhicules Client</button>
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
  if (!confirm("Voulez-vous vraiment supprimer ce client ?")) return; // ✅ Confirmation

  fetch(`/api/supprimer-client.php?id=${id}`, {
    method: "DELETE" // ❌ Supprime via API
  })
    .then(res => res.json())
    .then(data => {
      alert(data.message); // 💬 Message retour
      if (data.success) {
        chargerPage("clients.html"); // 🔄 Recharge la liste
      }
    })
    .catch(err => {
      alert("Erreur API suppression.");
      console.error("Erreur API suppression :", err);
    });
}



function afficherVehiculesClient(clientId) {
  const zone = document.getElementById("listVehicules");

  if (!zone) {
    console.warn("⚠️ #listVehicules introuvable dans la page");
    return;
  }

  fetch(`/api/vehicule-api.php?client_id=${clientId}`) // 🔄 Appel API pour les véhicules
    .then(res => res.json())
    .then(vehicules => {
      if (!Array.isArray(vehicules) || vehicules.length === 0) {
        zone.innerHTML = "<p>Aucun véhicule trouvé pour ce client.</p>";
        return;
      }

      zone.innerHTML = ""; // 🔄 Vide avant affichage
      vehicules.forEach(v => {
        const div = document.createElement("div"); // 📦 Carte véhicule
        div.className = "vehicule-card";
        div.innerHTML = `
          <strong>${v.marque} ${v.modele}</strong> - ${v.immatriculation}<br>
          Année : ${v.annee}
        `;
        zone.appendChild(div);
      });
    })
    .catch(err => {
      console.error("Erreur API véhicule :", err);
      zone.innerHTML = "<p>Erreur de chargement des véhicules.</p>";
    });
}

function initVehiculeForm(clientId) {
  const form = document.getElementById("vehiculeForm");       // 🧾 Formulaire véhicule
  const clientIdField = document.getElementById("client_id"); // 📌 Champ caché

  if (clientId && clientIdField) {
    clientIdField.value = clientId; // ✅ Injecte l’id du client dans l’input
    console.log("✅ ID client injecté :", clientId);
  }

  if (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault(); // ❌ Stop formulaire natif

      const formData = new FormData(form); // 📦 Données à envoyer

      fetch("/api/vehicule-api.php", {
        method: "POST",
        body: formData
      })
        .then(res => res.json())
        .then(data => {
          const msg = document.getElementById("messageVehicule");
          msg.textContent = data.message; // 📨 Affiche le message
          msg.style.color = data.success ? "green" : "red";

          if (data.success) {
            form.reset();                         // 🔄 Réinitialise le form
            clientIdField.value = clientId;       // 🔁 Réinjecte l’id
            afficherVehiculesClient(clientId);    // 🔄 Recharge la liste
          }
        })
        .catch(err => {
          console.error("Erreur API véhicule :", err);
          document.getElementById("messageVehicule").textContent = "Erreur serveur.";
        });
    });
  }
}
