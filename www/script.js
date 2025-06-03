function chargerPage(fichier, push = true) {
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

      // 🔁 Met à jour l'historique si demandé
      if (push) {
        history.pushState({ fichier }, "", "#" + fichier);
      }

      // === ROUTING LOGIC ===
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

      if (fichierBase === "travaux.html") {
        const urlParams = new URLSearchParams(params);
        const vehiculeId = urlParams.get("vehicule");

        if (vehiculeId) {
          initFormulaireTravaux(vehiculeId);
          afficherTravauxVehicule(vehiculeId);
        }
      }

      if (fichierBase === "addPiece.html" && params) {
        const urlParams = new URLSearchParams(params);
        const travailId = urlParams.get("id");
        if (travailId) {
          afficherPiece(travailId);
          initPiece(travailId);
        }
      }

      if (fichierBase === "home.html" || fichierBase === "dashboard.html") {
        afficherCalendrierTravaux();
      }
      if (fichierBase === "recettes.html") {
  afficherRecettes();
  }
if (fichierBase === "stats.html") {
  setTimeout(initStatsPage, 0);
}

if (fichierBase === "ajouterTravail.html") {
  initAjouterTravailPage();
}
if (fichierBase === "forfaits.html") {
  initForfaitPage();
}

if (fichierBase === "historiqueTravaux.html" && params) {
  const urlParams = new URLSearchParams(params);
  const vehiculeId = urlParams.get("vehicule");
  if (vehiculeId) {
    afficherHistoriqueTravaux(vehiculeId);

    // ✅ Rebranche bien l'event ici !
    const filtre = document.getElementById("filtrePeriode");
    if (filtre) {
      filtre.addEventListener("change", () => {
        afficherHistoriqueTravaux(vehiculeId);
      });
    }
  }
}


if (fichierBase === "historique.html") {
  afficherHistoriqueGlobal();
}


    })
    .catch(err => {
      target.innerHTML = "<p>Erreur lors du chargement de la page.</p>";
      console.error(err);
    });
}



function afficherClients() {
  fetch('/api/client-api.php')
    .then(res => res.json())
    .then(clients => {
      const zone = document.getElementById("clientListe");
      if (!zone) return;

      zone.innerHTML = "";

      clients.forEach(client => {
        const div = document.createElement("div");
        div.className = "client-card";
        div.dataset.nom = (client.nom + " " + client.prenom).toLowerCase();
      div.innerHTML = `
  <strong>${client.nom} ${client.prenom}</strong><br>
  Téléphone : ${client.telephone}<br>
  <button onclick="chargerPage('ficheClient.html?id=${client.id}')" class="btn-detail">📄 Voir fiche</button>
`;

        zone.appendChild(div);
      });

      // 🔍 Recherche dynamique
      const input = document.getElementById("searchClient");
      if (input) {
        input.addEventListener("input", () => {
          const q = input.value.toLowerCase();
          document.querySelectorAll(".client-card").forEach(card => {
            card.style.display = card.dataset.nom.includes(q) ? "block" : "none";
          });
        });
      }
    })
    .catch(err => {
      console.error("⛔ Erreur chargement clients :", err);
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
        showToast('Client introuvable');
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
  confirmDialog("Voulez vous vraiment supprimer ce client ?").then(confirm => {
  if (!confirm) return;


  fetch(`/api/supprimer-client.php?id=${id}`, {
    method: "DELETE" // ❌ Supprime via API
  })
    .then(res => res.json())
    .then(data => {
         
      if (data.success) {
        chargerPage("clients.html"); // 🔄 Recharge la liste
        showToast(data.message);
      }
    })
    .catch(err => {
      alert("Erreur API suppression.");
      console.error("Erreur API suppression :", err);
    });})
}


function afficherVehiculesClient(clientId) {
  const zone = document.getElementById("listVehicules");

  if (!zone) {
    console.warn("⚠️ #listVehicules introuvable dans la page");
    return;
  }

  fetch(`/api/vehicule-api.php?client_id=${clientId}`)
    .then(res => res.json())
    .then(vehicules => {
      if (!Array.isArray(vehicules) || vehicules.length === 0) {
        showToast('Aucun véhicule trouvé pour le client');
        zone.innerHTML = "<p>Aucun véhicule trouvé pour ce client.</p>";
        return;
      }

      zone.innerHTML = "";
      vehicules.forEach(v => {
        const div = document.createElement("div");
        div.className = "vehicule-card";
        div.innerHTML = `
          <strong>${v.marque} ${v.modele}</strong> - ${v.immatriculation}<br>
          Année : ${v.annee}
          <button onclick="chargerPage('travaux.html?vehicule=${v.id}')">Réparation</button>
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
      zone.innerHTML = ""; // 🔁 Reset complet

      // ✅ Toujours afficher le bouton historique
      const boutonHistorique = document.createElement("button");
      boutonHistorique.textContent = "📜 Voir l’historique";
      boutonHistorique.className = "btn-historique";
      boutonHistorique.onclick = () => {
        chargerPage(`historiqueTravaux.html?vehicule=${vehiculeId}`);
      };
      zone.appendChild(boutonHistorique);

      // Vérifie si y'a des travaux
      if (!Array.isArray(travaux) || travaux.length === 0) {
        const msg = document.createElement("p");
        msg.textContent = "Aucun travail trouvé pour ce véhicule.";
        zone.appendChild(msg);
        return;
      }

      const travauxActifs = travaux.filter(t => t.statut !== "terminé");

      if (travauxActifs.length === 0) {
        const msg = document.createElement("p");
        msg.textContent = "Aucun travail actif.";
        zone.appendChild(msg);
        return;
      }

      // 🔁 Affiche chaque travail actif
      travauxActifs.forEach(t => {
        const div = document.createElement("div");
        div.className = "travail-card";

        div.innerHTML = `
          <strong>${t.description}</strong><br>
          Début : ${t.date_debut || "-"}<br>
          Fin : ${t.date_fin || "-"}<br>
          Statut : ${t.statut}<br>

          <button onclick="chargerPage('addPiece.html?id=${t.id}')">✏️ Modifier/Ajouter une pièce</button>
          <button onclick="supprimerTravail(${t.id}, ${vehiculeId})" style="color:red;">🗑 Supprimer</button>
        `;

        if (!t.date_debut && t.statut !== "terminé") {
          div.innerHTML += `<button onclick="ouvrirModalDemarrer(${t.id}, ${vehiculeId})">▶️ Démarrer</button>`;
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
 confirmDialog("Voulez vous vraiment supprimer ce travail ?").then(confirm => {
  if (!confirm) return;

  fetch(`/api/travaux-api.php?travaux_id=${travauxId}`, {
    method: "DELETE"
  })
    .then(res => res.json())
    .then(data => {
       showToast(data.message);
      if (data.success) {
        // Recharge la liste après suppression
        // Il faut connaître le vehiculeId courant ici !
        afficherTravauxVehicule(vehiculeId);
        showToast(data.message);
      }
    })
    .catch(err => {
      showToast("Erreur lors de la suppression.");
      console.error("Erreur API suppression travail :", err);
    });})
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
       showToast(data.message);
      if (data.success) {
        afficherTravauxVehicule(document.getElementById("vehicule_id").value);
         showToast(data.message);
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
       showToast(data.message);
      if (data.success) {
        afficherTravauxVehicule(document.getElementById("vehicule_id").value);
         showToast(data.message);
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
       showToast(data.message);
      if (data.success) {
        afficherTravauxVehicule(document.getElementById("vehicule_id").value);
         showToast(data.message);
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
       showToast(data.message);
      if (data.success) {
        afficherTravauxVehicule(document.getElementById("vehicule_id").value);
         showToast(data.message);
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
        if (data.success) {
          form.reset();
          hiddenInput.value = travauxId;
          afficherPiece(travauxId);
          showToast(data.message);
        }
      })
      .catch(err => {
        console.error("Erreur API pièce :", err);
        message.textContent = "Erreur serveur.";
      });
  });

  afficherPiece(travauxId); // ✅ OBLIGATOIRE

  // ⬇️ ➕ AJOUTE TON CODE ICI 👇
  document.getElementById("forfaitSelect").addEventListener("change", e => {
    const option = e.target.selectedOptions[0];
    const info = document.getElementById("forfaitInfo");

    if (option && option.value) {
      info.innerHTML = `<p>✅ Forfait sélectionné : <strong>${option.textContent}</strong></p>`;
    } else {
      info.innerHTML = "";
    }

    const forfaitId = option.value;
    if (forfaitId) {
      fetch('/api/travaux-api.php?action=ajouter_forfait', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `id=${travauxId}&forfait_id=${forfaitId}`
      })
        .then(r => r.json())
        .then(data => {
          if (!data.success) {
            console.warn("❌ Erreur assignation forfait :", data.message);
          } else {
            showToast("✅ Forfait ajouté !");
          }
        });
    }
  });

  // Et ici en plus, charger dynamiquement les forfaits (si tu ne l’as pas déjà fait ailleurs) :
  fetch('/api/forfait-api.php')
    .then(res => res.json())
    .then(data => {
      const select = document.getElementById("forfaitSelect");
      if (!select) return;

      data.forEach(f => {
        const opt = document.createElement("option");
        opt.value = f.id;
        opt.textContent = `${f.nom} (${f.prix} €)`;
        select.appendChild(opt);
      });
    });
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
  confirmDialog("Voulez vous vraiment supprimer cette pièce ?").then(confirm=>{
if (!confirm) return;
  

  fetch(`/api/piece-api.php?piece_id=${pieceId}`, {
    method: "DELETE"
  })
    .then(res => res.json())
    .then(data => {
       showToast(data.message);
      if (data.success) {
        afficherPiece(travauxId); 
         showToast(data.message);
      }
    })
    .catch(err => {
      console.error("Erreur suppression pièce :", err);
    });})
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
  if (!toast) {
    console.warn("⚠️ Élément #toast introuvable dans le DOM !");
    return;
  }

  toast.textContent = message;
  toast.classList.add('show');

  setTimeout(() => {
    toast.classList.remove('show');
  }, duration);
}


function confirmDialog(message) {
  return new Promise(resolve => {
    const box = document.getElementById("confirmBox");
    const msg = document.getElementById("confirmMessage");
    const yes = document.getElementById("btnYes");
    const no = document.getElementById("btnNo");

    msg.textContent = message;
    box.classList.remove("hidden");

    const cleanUp = () => {
      box.classList.add("hidden");
      yes.removeEventListener("click", onYes);
      no.removeEventListener("click", onNo);
    };

    const onYes = () => { cleanUp(); resolve(true); };
    const onNo  = () => { cleanUp(); resolve(false); };

    yes.addEventListener("click", onYes);
    no.addEventListener("click", onNo);
  });
}
function afficherCalendrierTravaux() {
  fetch('/api/travaux-api.php?planning=1')
    .then(res => res.json())
    .then(travaux => {
      

      if (!Array.isArray(travaux)) {
        console.error("⛔ Format inattendu pour les travaux :", travaux);
        return;
      }

      // Helpers
      function dateStr(date) {
        return new Date(date).toLocaleDateString('fr-CA'); // YYYY-MM-DD
      }

      const aujourdHui = new Date();
      const demain = new Date();
      demain.setDate(aujourdHui.getDate() + 1);

      const todayStr = aujourdHui.toLocaleDateString('fr-CA');
      const tomorrowStr = demain.toLocaleDateString('fr-CA');

const zoneToday = document.querySelector("#sliderAujourdHui");
const zoneTomorrow = document.querySelector("#sliderDemain");


      if (!zoneToday || !zoneTomorrow) {
        console.warn("🛑 Zones calendrier introuvables !");
        return;
      }

      zoneToday.innerHTML = "";
      zoneTomorrow.innerHTML = "";

      travaux.forEach(t => {
        const tDateStr = dateStr(t.date_travail);

        const div = document.createElement("div");
        div.className = "travail-card";
div.innerHTML = `
  <div class="travail-header">
    <strong>${t.description}</strong>
    <div class="travail-heure">🕒 ${new Date(t.date_travail).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</div>
  </div>
<div class="travail-body">
  <p><strong>👤 Client :</strong> ${t.prenom} ${t.nom}</p>
<p>
  <strong>📞 Téléphone :</strong>
  <span class="phone-value">${t.telephone}</span>
  <button class="copy-btn" onclick="copyToClipboard('${t.telephone}')">📋</button>
  <button class="add-travail-btn" onclick="chargerPage('travaux.html?vehicule=${t.vehicule_id}')">➕</button>
</p>


  <p><strong>🚗 Véhicule :</strong> ${t.marque} ${t.modele}</p>
</div>

`;

        if (tDateStr === todayStr) {
          zoneToday.appendChild(div);
        } else if (tDateStr === tomorrowStr) {
          zoneTomorrow.appendChild(div);
        }
      });

    })
    .catch(err => {
      console.error("⛔ Erreur chargement calendrier :", err);
    });
}




function slideTravaux(jour, direction) {
  const slider = document.getElementById(`slider${jour.charAt(0).toUpperCase() + jour.slice(1)}`);
  const cardWidth = 260; // ≈ largeur + margin
  slider.scrollLeft += direction * cardWidth;
}


function copyToClipboard(text) {
  navigator.clipboard.writeText(text).then(() => {
    showToast("📞 Numéro copié !");
  }).catch(err => {
    console.error("Erreur copie :", err);
    showToast("❌ Échec de la copie");
  });
}


function login(e) {
  e.preventDefault();

  const username = document.getElementById("username").value.trim();
  const password = document.getElementById("password").value.trim();

  const formData = new FormData();
  formData.append("username", username);
  formData.append("password", password);

  fetch("/api/auth.php", {
    method: "POST",
    body: formData
  })
    .then(res => res.json())
.then(data => {
  if (data.success && data.redirect) {
    showToast("✅ Connexion réussie !");
    window.location.href = data.redirect; // ⬅️ redirection propre
  } else {
    showToast("❌ " + (data.message || "Erreur inconnue"));
  }
})

    .catch(err => {
      console.error("Erreur réseau :", err);
      showToast("⚠️ Serveur injoignable !");
    });
}

window.addEventListener("popstate", (event) => {
  if (event.state && event.state.fichier) {
    chargerPage(event.state.fichier, false); // 🔁 Ne push pas à nouveau
  }
});

function retourArriere() {
  window.history.back(); // ← va dans l'historique du navigateur
}

function afficherRecettes() {
  fetch('/api/recettes-api.php')
    .then(res => res.json())
    .then(data => {
      const zone = document.querySelector(".recette-liste");
      if (!zone) return;

      zone.innerHTML = "";

      if (!Array.isArray(data) || data.length === 0) {
        zone.innerHTML = "<p>Aucune recette à afficher.</p>";
        return;
      }

      data.forEach(recette => {
        const total = parseFloat(recette.total || 0).toFixed(2); // 💸 Sécurisé

        const div = document.createElement("div");
        div.className = "recette-card";
        div.innerHTML = `
          <strong>${recette.description}</strong><br>
          Client : ${recette.prenom} ${recette.nom}<br>
          Date : ${new Date(recette.date_travail).toLocaleDateString()}<br>
          Montant : <strong>${total} €</strong><br>
          <button onclick="reglerTravail(${recette.id})" class="btn-regler">✅ Régler</button>
        `;

        zone.appendChild(div);
      });
    })
    .catch(err => {
      console.error("⛔ Erreur chargement recettes :", err);
    });
}




function reglerTravail(travailId) {
 confirmDialog("Valider le paiement du client").then(confirm=>{
if (!confirm) return;

  fetch('/api/travaux-api.php?action=regler', {
    method: "POST",
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: `id=${travailId}`
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        showToast("💸 Travail marqué comme réglé !");
        afficherRecettes(); // Refresh
      } else {
        showToast("❌ " + (data.message || "Erreur lors du règlement"));
      }
    })
    .catch(err => {
      console.error("⛔ Erreur réseau :", err);
      showToast("⚠️ Erreur serveur");
    });})
}

function initStatsPage() {
  const select = document.getElementById("moisSelect");

  fetch('/api/mois-disponibles.php')
    .then(res => res.json())
    .then(data => {
      if (data.success && Array.isArray(data.mois)) {
        select.innerHTML = `<option value="">Tous</option>`;
        data.mois.forEach(m => {
          const label = new Date(m + "-01").toLocaleDateString("fr-FR", {
            year: "numeric", month: "long"
          });
          select.innerHTML += `<option value="${m}">${label}</option>`;
        });
      }
    });

  // 🔁 À chaque changement de mois
  select.addEventListener("change", () => {
    afficherStats(select.value);
  });

  afficherStats(); // Chargement initial
}


function afficherStats(mois = "") {
  let url = '/api/stats-api.php';

  if (mois) {
    url += `?periode=mois&mois=${encodeURIComponent(mois)}`;
  }

  fetch(url)
    .then(res => res.json())
    .then(stats => {
      const zone = document.getElementById("statsZone");
      if (!zone) return;

      zone.innerHTML = `
        <p>🕐 Total heures travaillées : <strong>${(parseFloat(stats.total_heures || 0)).toFixed(1)} h</strong></p>
        <p>✅ Montant réglé : <strong>${(parseFloat(stats.total || 0)).toFixed(2)} €</strong></p>
        <p>🧾 Montant à encaisser : <strong>${(parseFloat(stats.total_prevu || 0)).toFixed(2)} €</strong></p>
      `;

      if (stats.par_mois) {
        updateGraph(stats.par_mois);
      }
    })
    .catch(err => {
      console.error("⛔ Erreur chargement stats :", err);
    });
}


let statsChart = null;


let graph; // scope global

function updateGraph(data) {
  const canvas = document.getElementById("graphStats");
  if (!canvas) {
    console.warn("📊 Le canvas #graphStats est introuvable !");
    return;
  }

  const ctx = canvas.getContext("2d");

  const labels = Object.keys(data);
  const values = Object.values(data);

  if (graph) graph.destroy(); // 🔄 évite doublons

  graph = new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: '€ par mois',
        data: values,
        backgroundColor: 'rgba(33, 150, 243, 0.6)',
        borderRadius: 6
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: true }
      }
    }
  });
}



function initAjouterTravailPage() {
  const clientSelect = document.getElementById("clientSelect");
  const vehiculeSelect = document.getElementById("vehiculeSelect");
  const vehiculeInput = document.getElementById("vehicule_id");
  const form = document.getElementById("formTravailGlobal");
  const message = document.getElementById("messageTravail");

  // 🔄 Charger tous les clients
  fetch('/api/client-api.php')
    .then(res => res.json())
    .then(clients => {
      clientSelect.innerHTML = '<option value="">-- Sélectionner un client --</option>';
      clients.forEach(client => {
        clientSelect.innerHTML += `<option value="${client.id}">${client.nom} ${client.prenom}</option>`;
      });
    });

  // 🧲 Quand on change de client, on charge ses véhicules
clientSelect.addEventListener("change", () => {
  const clientId = clientSelect.value;

  // ✅ Injecte dans le champ caché
  document.getElementById("client_id").value = clientId;

  vehiculeSelect.innerHTML = '<option value="">Chargement...</option>';
  vehiculeInput.value = "";

  fetch(`/api/vehicule-api.php?client_id=${clientId}`)
    .then(res => res.json())
    .then(vehicules => {
      vehiculeSelect.innerHTML = '<option value="">-- Sélectionner un véhicule --</option>';
      vehicules.forEach(v => {
        vehiculeSelect.innerHTML += `<option value="${v.id}">${v.marque} ${v.modele} (${v.immatriculation})</option>`;
      });

      if (vehicules.length > 0) {
        vehiculeSelect.value = vehicules[0].id;
        vehiculeInput.value = vehicules[0].id;
      }
    });
});


  // 🔁 Quand on sélectionne un véhicule
  vehiculeSelect.addEventListener("change", () => {
    vehiculeInput.value = vehiculeSelect.value;
  });

  // 📤 Soumission
  form.addEventListener("submit", e => {
    e.preventDefault();
    const formData = new FormData(form);

    fetch('/api/ajout-travail-api.php', {
      method: "POST",
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        message.textContent = data.message;
        message.style.color = data.success ? "green" : "red";
        if (data.success) {
          form.reset();
          vehiculeInput.value = "";
        }
      })
      .catch(err => {
        message.textContent = "Erreur serveur 🔥";
        console.error("❌ Erreur API ajout travail :", err);
      });
  });
  // 🔄 Charger les forfaits disponibles
fetch("/api/forfait-api.php")
  .then(res => res.json())
  .then(data => {
    const forfaitSelect = document.getElementById("forfaitSelect");
    data.forEach(f => {
      const opt = document.createElement("option");
      opt.value = f.id;
      opt.textContent = `${f.nom} (${f.prix} €)`;
      forfaitSelect.appendChild(opt);
    });
  });

}

function initForfaitPage() {
  const form = document.getElementById("formForfait");
  const tableBody = document.querySelector("#forfaitTable tbody");
  const message = document.getElementById("messageForfait");

  function loadForfaits() {
    fetch('/api/forfait-api.php')
      .then(res => res.json())
      .then(data => {
        tableBody.innerHTML = "";
        data.forEach(f => {
          const row = document.createElement("tr");
          row.innerHTML = `
            <td>${f.nom}</td>
            <td>${f.prix} €</td>
            <td><button onclick="supprimerForfait(${f.id})">🗑️ Supprimer</button></td>
          `;
          tableBody.appendChild(row);
        });
      });
  }

  form.addEventListener("submit", e => {
    e.preventDefault();
    const formData = new FormData(form);

    fetch('/api/forfait-api.php', {
      method: "POST",
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      message.textContent = data.message;
      message.style.color = data.success ? "green" : "red";
      if (data.success) {
        form.reset();
        loadForfaits();
      }
    });
  });

  window.supprimerForfait = (id) => {
    fetch('/api/forfait-api.php', {
      method: "DELETE",
      body: new URLSearchParams({ id })
    })
    .then(res => res.json())
    .then(data => {
      message.textContent = data.message;
      loadForfaits();
    });
  }

  loadForfaits();
}


let currentTravailId = null;
let currentVehiculeId = null;

function ouvrirModalDemarrer(id, vehiculeId) {
  currentTravailId = id;
  currentVehiculeId = vehiculeId;
  document.getElementById("modalTaux").style.display = "flex";
}

function fermerModalTaux() {
  document.getElementById("modalTaux").style.display = "none";
  document.getElementById("inputTaux").value = "";
}


document.getElementById("btnDemarrerTravail").addEventListener("click", () => {
  const taux = document.getElementById("inputTaux").value;
  if (!taux) return alert("Veuillez entrer un taux horaire.");

  fetch(`/api/travaux-api.php?action=demarrer`, {
    method: "POST",
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `id=${currentTravailId}&taux=${taux}`
  })
    .then(r => r.json())
    .then(data => {
      fermerModalTaux();
      afficherTravauxVehicule(currentVehiculeId);
      showToast(data.message || "Travail démarré.");
    });
});

document.getElementById("btnFinaliserDirect").addEventListener("click", () => {
  fetch(`/api/travaux-api.php?action=finaliser`, {
    method: "POST",
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `id=${currentTravailId}`
  })
    .then(r => r.json())
    .then(data => {
      fermerModalTaux();
      afficherTravauxVehicule(currentVehiculeId);
      showToast(data.message || "Travail finalisé.");
    });
});



function afficherHistoriqueTravaux(vehiculeId) {
  const periode = document.getElementById("filtrePeriode").value;

  let url = `/api/travaux-api.php?historique=1&vehicule_id=${vehiculeId}`;
  if (periode !== "all") {
    url += `&jours=${periode}`;
  }

  console.log("📡 API URL:", url); // 👀 debug

  fetch(url)
    .then(res => res.json())
    .then(data => {
      const zone = document.getElementById("historiqueTravaux");
      if (!data.length) {
        zone.innerHTML = "<p>Aucun travail trouvé.</p>";
        return;
      }

      zone.innerHTML = "";
      data.forEach(t => {
        const div = document.createElement("div");
        div.className = "travail-card";
        div.innerHTML = `
          <strong>${t.description}</strong><br>
          📆 ${t.date_fin}<br>
          💶 ${t.total} €
        `;
        zone.appendChild(div);
      });
    });
}

document.addEventListener("DOMContentLoaded", () => {
  const zone = document.getElementById("historiqueTravaux");
  if (zone && zone.dataset.id) {
    currentVehiculeId = zone.dataset.id;
    afficherHistoriqueTravaux(currentVehiculeId);

    const filtre = document.getElementById("filtrePeriode");
    if (filtre) {
      filtre.addEventListener("change", () => {
        afficherHistoriqueTravaux(currentVehiculeId);
      });
    }
  }
});




function afficherHistoriqueGlobal() {
  const zone = document.getElementById("historiqueGlobal");
  zone.innerHTML = "Chargement...";

  fetch("/api/travaux-api.php?historique_global=1")
    .then(res => res.json())
    .then(data => {
      if (!Array.isArray(data) || data.length === 0) {
        zone.innerHTML = "<p>⚠️ Aucun travail terminé trouvé.</p>";
        return;
      }

      const groupByDate = {};

      data.forEach(t => {
        const date = t.date_fin?.split(" ")[0] || "Inconnue";
        if (!groupByDate[date]) groupByDate[date] = [];
        groupByDate[date].push(t);
      });

      zone.innerHTML = "";
      for (const date in groupByDate) {
        const section = document.createElement("div");
        section.className = "jour-historique";
        section.innerHTML = `<h3>📅 ${date}</h3>`;

        groupByDate[date].forEach(t => {
          section.innerHTML += `
            <div class="travail-card">
              🔧 ${t.description}<br>
              👤 ${t.nom} ${t.prenom}<br>
              🚗 ${t.marque} ${t.modele}<br>
              🧾 Total : ${t.total} €
            </div>
          `;
        });

        zone.appendChild(section);
      }
    })
    .catch(err => {
      zone.innerHTML = "<p>💥 Erreur lors du chargement de l’historique</p>";
      console.error("❌ Erreur API historique :", err);
    });
}
