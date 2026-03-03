import 'dart:convert';
import 'package:http/http.dart' as http;
import '../models/build_model.dart';

class ApiService {
  static const String baseUrl = "http://10.176.128.195:8080/api"; 

  Future<List<Build>> fetchBuilds() async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/builds'));

      if (response.statusCode == 200) {
        final Map<String, dynamic> responseData = jsonDecode(response.body);
        
        final List<dynamic> buildsJson = responseData['data']; 
        
        return buildsJson.map((item) => Build.fromJson(item)).toList();
      } else {
        throw Exception("Erreur serveur : ${response.statusCode}");
      }
    } catch (e) {
      throw Exception("Erreur de connexion : $e");
    }
  }
}