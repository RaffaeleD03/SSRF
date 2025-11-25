# SSRF Vulnerability Demo & Analysis
Docker-based simulation of a Server-Side Request Forgery (SSRF) attack. Demonstrates vulnerability exploitation, credential exfiltration, and remediation techniques using PHP and Apache.


**Progetto didattico di Cybersecurity: Analisi, riproduzione e mitigazione di una vulnerabilità SSRF (Server-Side Request Forgery).**

Questo repository contiene un ambiente virtualizzato basato su **Docker** che simula uno scenario reale di attacco SSRF. Il progetto dimostra come un attaccante esterno possa sfruttare un server web vulnerabile per aggirare la segmentazione di rete ed esfiltrare credenziali cloud sensibili da un server interno isolato.

---

## Architettura del Laboratorio (Testbed)

L'ambiente è composto da 4 container isolati tramite due reti virtuali (`rete_pubblica` e `rete_privata`) per simulare una DMZ e una rete interna protetta.

| Servizio | Nome Container | IP / Rete | Ruolo |
| :--- | :--- | :--- | :--- |
| **Attaccante** | `attaccante` | Rete Pubblica | Container Alpine con `curl` per simulare l'agente di minaccia. |
| **Vittima (Proxy)** | `server_vulnerabile` | Dual-Homed (Pubblica + Privata) | Server Web PHP vulnerabile a SSRF. Funge da "ponte" involontario. |
| **Target (Admin)** | `server_admin` | **Rete Privata (Isolato)** | Server interno non accessibile da internet. Contiene il file segreto `key.json`. |
| **Test Server** | `server_test` | Rete Pubblica | Simula un sito internet legittimo (es. Google) per testare il funzionamento normale. |

---

## Installazione e Avvio

### Prerequisiti
* Docker
* Docker Compose

### Avvio dell'ambiente
Clona la repository ed esegui i container in background:

```bash
git clone [https://github.com/RaffaeleD03/SSRF.git](https://github.com/RaffaeleD03/SSRF.git)
cd SSRF
docker-compose up -d
