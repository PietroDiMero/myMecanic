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

      if (fichierBase === "vehicules.html") {
        const urlParams = new URLSearchParams(params);
        const clientId = urlParams.get("client");

        afficherVehiculesClient(clientId);
        initVehiculeForm(clientId);
      }

      // ✅ Le bloc à AJOUTER pour travaux.html
      if (fichierBase === "travaux.html") {
        const urlParams = new URLSearchParams(params);
        const vehiculeId = urlParams.get("vehicule");

        if (vehiculeId) {
          initFormulaireTravaux(vehiculeId);      // charge le formulaire
          afficherTravauxVehicule(vehiculeId);     // si tu veux aussi afficher la liste
        }
      }

if (fichierBase === "addPiece.html" && params) {
  const urlParams = new URLSearchParams(params);
  const travailId = urlParams.get("id");
  if (travailId) {
    afficherPiece(travailId);
    initPiece(travailId); // 🔧 Lancement de l’init
  }
}


    })
    .catch(err => {
      target.innerHTML = "<p>Erreur lors du chargement de la page.</p>";
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
  <div class="client-detail-card">
    <h2>Fiche client</h2>
    <div class="client-info">
      <h3>${client.prenom} ${client.nom}</h3>
      <p><span class="icon">📞</span> <strong>Téléphone :</strong> ${client.telephone}</p>
      <p><span class="icon">🕒</span> <strong>Date d'ajout :</strong> ${client.date_creation}</p>
    </div>

    <div class="client-actions">
      <button onclick="chargerPage('vehicules.html?client=${client.id}')">Véhicules Client</button>
      <button onclick="supprimerClient(${client.id})" class="danger-button">🗑 Supprimer</button>
      <button onclick="chargerPage('clients.html')">⬅ Retour</button>
    </div>
  </div>
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
          <button onclick="chargerPage('travaux.html?vehicule=${v.id}')"> Réparation </button>
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

function initFormulaireTravaux(vehiculeId) {
  const form = document.getElementById("formTravail");
  const vehiculeInput = document.getElementById("vehicule_id");
  const clientInput = document.getElementById("client_id");
  const message = document.getElementById("messageTravail");

  // 🔄 Aller chercher le client_id depuis le véhicule
  fetch(`/api/vehicule-api.php?client_id_from_vehicule=${vehiculeId}`)
    .then(res => res.json())
    .then(data => {
      if (data.client_id && clientInput && vehiculeInput) {
        clientInput.value = data.client_id;
        vehiculeInput.value = vehiculeId;
      } else {
        console.warn("Client ID non trouvé pour ce véhicule.");
      }
    });

  // 📤 Soumission du formulaire
  if (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();

      const formData = new FormData(form);

      fetch("/api/travaux-api.php", {
        method: "POST",
        body: formData
      })
        .then(res => res.json())
        .then(data => {
          message.textContent = data.message;
          message.style.color = data.success ? "green" : "red";

if (data.success) {
  form.reset();
  afficherTravauxVehicule(vehiculeId);     // Recharge les travaux
  vehiculeInput.value = vehiculeId;

  // 🔽 Scroll vers la liste des travaux
  const zone = document.getElementById("listeTravaux");
  if (zone) zone.scrollIntoView({ behavior: "smooth" });
}

        })
        .catch(err => {
          console.error("Erreur API travaux :", err);
          message.textContent = "Erreur serveur.";
        });
    });
  }
}


function afficherTravauxVehicule(vehiculeId) {
  const zone = document.getElementById("listeTravaux");
  if (!zone) {
    console.warn("⚠️ #listeTravaux manquant");
    return;
  }

  fetch(`/api/travaux-api.php?vehicule_id=${vehiculeId}`)
    .then(res => res.json())
    .then(travaux => {
      if (!Array.isArray(travaux) || travaux.length === 0) {
        zone.innerHTML = "<p>Aucun travail trouvé pour ce véhicule.</p>";
        return;
      }

      zone.innerHTML = "";
      travaux.forEach(t => {
        const div = document.createElement("div");
        div.className = "travail-card";
        div.innerHTML = `
          <strong>${t.description}</strong> <br>
          Début : ${t.date_debut || "-"} <br>
          Fin : ${t.date_fin || "-"} <br>
          Statut : ${t.statut}

           <button onclick="chargerPage('addPiece.html?id=${t.id}')"> ✏️ Modifier/Ajouter une pièce </button>
            <button onclick="supprimerTravail(${t.id}, ${vehiculeId})" style="color:red;">🗑 Supprimer</button>
        `;
     if (!t.date_debut) {
  div.innerHTML += `<button onclick="demarrerTravail(${t.id})">▶️ Démarrer</button>`;
} else if (t.date_debut && !t.date_fin) {
  if (t.en_pause) {
    div.innerHTML += `<button onclick="reprendreTravail(${t.id})">▶️ Reprendre</button>`;
  } else {
    div.innerHTML += `<button onclick="pauseTravail(${t.id})">⏸️ Pause</button>`;
    div.innerHTML += `<button onclick="finaliserTravail(${t.id})" style="color:green;">✅ Finaliser</button>`;
  }
}



        zone.appendChild(div);
      });
    })
    .catch(err => {
      console.error("Erreur chargement travaux :", err);
      zone.innerHTML = "<p>Erreur serveur</p>";
    });
}


function supprimerTravail(travauxId, vehiculeId) {
  if (!confirm("Voulez-vous vraiment supprimer ce travail ?")) return;

  fetch(`/api/travaux-api.php?travaux_id=${travauxId}`, {
    method: "DELETE"
  })
    .then(res => res.json())
    .then(data => {
      alert(data.message);
      if (data.success) {
        // Recharge la liste après suppression
        // Il faut connaître le vehiculeId courant ici !
        afficherTravauxVehicule(vehiculeId);
      }
    })
    .catch(err => {
      alert("Erreur lors de la suppression.");
      console.error("Erreur API suppression travail :", err);
    });
}

function finaliserTravail(id) {
  fetch(`/api/travaux-api.php?action=finaliser`, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body: `id=${encodeURIComponent(id)}`
  })
    .then(res => res.json())
    .then(data => {
      alert(data.message);
      if (data.success) {
        afficherTravauxVehicule(document.getElementById("vehicule_id").value);
      }
    })
    .catch(err => {
      console.error("Erreur finalisation :", err);
    });
}


function demarrerTravail(travailId) {
  fetch("/api/travaux-api.php?action=demarrer", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body: `id=${encodeURIComponent(travailId)}`
  })
    .then(res => res.json())
    .then(data => {
      alert(data.message);
      if (data.success) {
        afficherTravauxVehicule(document.getElementById("vehicule_id").value);
      }
    })
    .catch(err => {
      console.error("Erreur démarrage :", err);
    });
}


function pauseTravail(id) {
  fetch(`/api/travaux-api.php?action=pause&id=${id}`, {
    method: "POST"
  })
    .then(res => res.json())
    .then(data => {
      alert(data.message);
      if (data.success) {
        afficherTravauxVehicule(document.getElementById("vehicule_id").value);
      }
    })
    .catch(err => {
      console.error("Erreur lors de la mise en pause :", err);
    });
}

function reprendreTravail(id) {
  fetch(`/api/travaux-api.php?action=reprendre&id=${id}`, {
    method: "POST"
  })
    .then(res => res.json())
    .then(data => {
      alert(data.message);
      if (data.success) {
        afficherTravauxVehicule(document.getElementById("vehicule_id").value);
      }
    })
    .catch(err => {
      console.error("Erreur lors de la reprise :", err);
    });
}


function initPiece(travauxId) {
  const form = document.getElementById('addPiece');
  const hiddenInput = document.getElementById('travail_id');
  const message = document.getElementById('messagePiece');

  if (!form || !hiddenInput) {
    console.error("Formulaire ou champ caché manquant");
    return;
  }

  // Injecte l’ID dans le champ caché
  hiddenInput.value = travauxId;

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(form);

    fetch('/api/piece-api.php', {
      method: 'POST',
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        message.textContent = data.message;
        message.style.color = data.success ? 'green' : 'red';

        if (data.success) {
          form.reset();
          hiddenInput.value = travauxId; // Réinjecte après reset
          afficherPiece(travauxId);
        }
      })
      .catch(err => {
        console.error("Erreur API pièce :", err);
        message.textContent = "Erreur serveur.";
      });
  });

  afficherPiece(travauxId);
}





function afficherPiece(travauxId) {
  const zone = document.getElementById("listePieces"); // Assure-toi que cette div existe dans ton HTML
  if (!zone) {
    console.warn("⚠️ #listePieces manquant");
    return;
  }

  fetch(`/api/piece-api.php?travail_id=${travauxId}`)
    .then(res => res.json())
    .then(pieces => {
      if (!Array.isArray(pieces) || pieces.length === 0) {
        zone.innerHTML = "<p>Aucune pièce enregistrée.</p>";
        return;
      }

      zone.innerHTML = ""; // Vider avant affichage
      pieces.forEach(piece => {
        const div = document.createElement("div");
        div.className = "piece-card";
        div.innerHTML = `
        <strong>${piece.nom_piece}</strong> - ${parseFloat(piece.prix).toFixed(2)} €

          <br>
          <button onclick="supprimerPiece(${piece.id}, ${travauxId})" style="color:red;">🗑 Supprimer</button>
        `;
        zone.appendChild(div);
      });
    })
    .catch(err => {
      console.error("Erreur chargement pièces :", err);
      zone.innerHTML = "<p>Erreur serveur</p>";
    });
}

function supprimerPiece(pieceId, travauxId) {
  if (!confirm("Supprimer cette pièce ?")) return;

  fetch(`/api/piece-api.php?piece_id=${pieceId}`, {
    method: "DELETE"
  })
    .then(res => res.json())
    .then(data => {
      alert(data.message);
      if (data.success) {
        afficherPiece(travauxId); // 🔄 recharge la liste
      }
    })
    .catch(err => {
      console.error("Erreur suppression pièce :", err);
    });
}

function toggleMenu(force) {
  const menu = document.getElementById('sideMenu');
  const overlay = document.getElementById('menuOverlay');
  const open = typeof force === 'boolean' ? force : !menu.classList.contains('open');

  if (open) {
    menu.classList.add('open');
    overlay.classList.add('visible');
  } else {
    menu.classList.remove('open');
    overlay.classList.remove('visible');
  }
}


function showToast(message, duration = 3000) {
  const toast = document.getElementById('toast');
  toast.textContent = message;
  toast.classList.add('show');

  setTimeout(() => {
    toast.classList.remove('show');
  }, duration);
}


