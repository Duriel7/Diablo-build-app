import 'package:flutter/material.dart';

const String apiIp = String.fromEnvironment('API_IP', defaultValue: 'localhost');
const String apiPort = String.fromEnvironment('API_PORT', defaultValue: '8080');
const String baseImageUrl = "http://$apiIp:$apiPort/uploads/avatars";

class ProfilePage extends StatelessWidget {
  final Map<String, dynamic> user;

  const ProfilePage({super.key, required this.user});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF1A1A1A),
      appBar: AppBar(
        title: const Text("PROFIL DU NEPHALEM"),
        backgroundColor: Colors.black,
      ),
      body: SingleChildScrollView(
        child: Column(
          children: [
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(20),
              decoration: const BoxDecoration(
                color: Colors.black,
                border: Border(bottom: BorderSide(color: Color(0xFFC5A059), width: 2)),
              ),
              child: Column(
                children: [
                  CircleAvatar(
                    radius: 60,
                    backgroundColor: const Color(0xFFC5A059),
                    child: CircleAvatar(
                      radius: 58,
                      backgroundImage: (user['avatar'] != null && !user['avatar'].contains('user.png'))
                          ? NetworkImage("$baseImageUrl/${user['avatar']}")
                          : const AssetImage('assets/img/default-avatar.png') as ImageProvider,
                    ),
                  ),
                  const SizedBox(height: 15),
                  Text(
                    user['nickname']?.toUpperCase() ?? "INCONNU",
                    style: const TextStyle(color: Color(0xFFC5A059), fontSize: 26, fontWeight: FontWeight.bold),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.blueAccent.withValues(alpha: 0.2),
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(color: Colors.blueAccent),
                    ),
                    child: Text(
                      user['role']?.toUpperCase() ?? "VISITEUR",
                      style: const TextStyle(color: Colors.blueAccent, fontSize: 12),
                    ),
                  ),
                ],
              ),
            ),

            // Détails du profil
            Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildInfoTile(Icons.calendar_today, "Inscrit le", user['registeredAt'] ?? "Inconnue"),
                  _buildInfoTile(Icons.location_city, "Ville", user['city'] ?? "Non renseignée"),
                  _buildInfoTile(Icons.map, "Coordonnées", "Lat: ${user['latitude']} / Long: ${user['longitude']}"),
                  const Divider(color: Colors.grey, height: 40),
                  const Text("BIOGRAPHIE", style: TextStyle(color: Color(0xFFC5A059), fontWeight: FontWeight.bold)),
                  const SizedBox(height: 10),
                  Text(
                    user['bio'] ?? "Ce héros n'a pas encore écrit sa légende.",
                    style: const TextStyle(color: Colors.white70, fontSize: 16, fontStyle: FontStyle.italic),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoTile(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 10),
      child: Row(
        children: [
          Icon(icon, color: const Color(0xFFC5A059), size: 20),
          const SizedBox(width: 15),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(label, style: const TextStyle(color: Colors.grey, fontSize: 12)),
              Text(value, style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.w500)),
            ],
          ),
        ],
      ),
    );
  }
}