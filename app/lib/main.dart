import 'package:flutter/foundation.dart'; // ← agrega esto arriba con los imports
import 'dart:io' show Platform;
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

void main() => runApp(const JJVVApp());

class JJVVApp extends StatelessWidget {
  const JJVVApp({super.key});
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'JJVV La Flecha',
      theme: ThemeData(
        fontFamily: 'Nunito',
        primarySwatch: Colors.teal,
        useMaterial3: true,
      ),
      home: const HomeScreen(),
      debugShowCheckedModeBanner: false,
    );
  }
}

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});
  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  List<String> fotos = [];
  List<dynamic> proximos = [];
  bool cargando = true;
  String? errorMensaje;

  static String get dominio {
  if (kIsWeb) return "http://juntavecinos.test";
  if (Platform.isAndroid) return "http://10.0.2.2/juntavecinos";
  return "http://192.168.1.37/juntavecinos"; // ← TU IP AQUÍ
}

  @override
  void initState() {
    super.initState();
    cargarDatos();
  }

  Future<void> cargarDatos() async {
    setState(() {
      cargando = true;
      errorMensaje = null;
    });

    try {
      final respuestas = await Future.wait([
        http.get(Uri.parse("$dominio/api/fotos.php")),
        http.get(Uri.parse("$dominio/api/proximos.php")),
      ]);

      final fotosRaw = json.decode(respuestas[0].body);
      final eventosRaw = json.decode(respuestas[1].body);

      setState(() {
        fotos = (fotosRaw as List).map((r) => "$dominio/$r".replaceAll("\\", "/")).toList();
        proximos = eventosRaw as List;
        cargando = false;
      });
    } catch (e) {
      setState(() {
        cargando = false;
        errorMensaje = "Error de conexión: $e";
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      body: cargando
          ? const Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  CircularProgressIndicator(color: Color(0xFF10B981), strokeWidth: 6),
                  SizedBox(height: 20),
                  Text("Cargando datos...", style: TextStyle(fontSize: 18)),
                ],
              ),
            )
          : errorMensaje != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.error, size: 80, color: Colors.red),
                      const SizedBox(height: 20),
                      Text(errorMensaje!, textAlign: TextAlign.center, style: const TextStyle(fontSize: 18)),
                      const SizedBox(height: 20),
                      ElevatedButton.icon(
                        onPressed: cargarDatos,
                        icon: const Icon(Icons.refresh),
                        label: const Text("Reintentar"),
                        style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF10B981)),
                      ),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: cargarDatos,
                  color: const Color(0xFF10B981),
                  child: CustomScrollView(
                    slivers: [
                      // HEADER
                      SliverAppBar(
                        expandedHeight: 220,
                        floating: false,
                        pinned: true,
                        flexibleSpace: FlexibleSpaceBar(
                          title: const Text('JJVV LA FLECHA', style: TextStyle(fontWeight: FontWeight.w900, color: Colors.white)),
                          background: Container(
                            decoration: const BoxDecoration(
                              gradient: LinearGradient(colors: [Color(0xFF10B981), Colors.teal], begin: Alignment.topLeft, end: Alignment.bottomRight),
                            ),
                          ),
                        ),
                      ),

                      // CARRUSEL - AHORA SÍ SE VEN TODAS LAS FOTOS
if (fotos.isNotEmpty)
  SliverToBoxAdapter(
    child: Container(
      height: 340,
      margin: const EdgeInsets.all(12),
      child: PageView.builder(
        padEnds: false,
        pageSnapping: true,
        controller: PageController(viewportFraction: 0.88), // ← ESTO HACE LA MAGIA
        itemCount: fotos.length,
        itemBuilder: (_, i) => Container(
          margin: const EdgeInsets.symmetric(horizontal: 8),
          child: ClipRRect(
            borderRadius: BorderRadius.circular(20),
            child: Image.network(
              fotos[i],
              fit: BoxFit.cover,
              loadingBuilder: (_, child, progress) => progress == null
                  ? child
                  : const Center(child: CircularProgressIndicator(color: Color(0xFF10B981))),
              errorBuilder: (_, __, ___) => Container(
                color: Colors.grey[300],
                child: const Icon(Icons.broken_image, size: 60),
              ),
            ),
          ),
        ),
      ),
    ),
  ),

                      const SliverToBoxAdapter(child: SizedBox(height: 20)),

                      // TÍTULO EVENTOS
                      const SliverToBoxAdapter(
                        child: Padding(
                          padding: EdgeInsets.fromLTRB(20, 10, 20, 10),
                          child: Text("Próximos Eventos", style: TextStyle(fontSize: 30, fontWeight: FontWeight.w900, color: Colors.teal)),
                        ),
                      ),

                      // LISTA EVENTOS
                      SliverList(
                        delegate: SliverChildBuilderDelegate(
                          (context, i) {
                            final e = proximos[i];
                            final fecha = DateTime.parse(e['fecha']);
                            return Card(
                              margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
                              elevation: 6,
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                              child: ListTile(
                                contentPadding: const EdgeInsets.all(18),
                                leading: CircleAvatar(
                                  radius: 36,
                                  backgroundColor: Colors.teal,
                                  child: Text(fecha.day.toString(), style: const TextStyle(fontSize: 34, fontWeight: FontWeight.bold, color: Colors.white)),
                                ),
                                title: Text(e['nombre'], style: const TextStyle(fontSize: 19, fontWeight: FontWeight.bold)),
                                subtitle: Text("${e['inicio'].substring(0, 5)} - ${e['fin'].substring(0, 5)} hrs", style: const TextStyle(fontSize: 17)),
                                trailing: const Icon(Icons.arrow_forward_ios, color: Colors.teal),
                              ),
                            );
                          },
                          childCount: proximos.length,
                        ),
                      ),

                      const SliverToBoxAdapter(child: SizedBox(height: 100)),
                    ],
                  ),
                ),
    );
  }
}