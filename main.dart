/// lib/main.dart
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:intl/intl.dart';
import 'package:intl/date_symbol_data_local.dart';
import 'dart:convert';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:photo_view/photo_view.dart';
import 'package:photo_view/photo_view_gallery.dart';

void main() {
  initializeDateFormatting('es_ES'); // ← importante para meses en español
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'JJVV La Flecha',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        fontFamily: 'Nunito', // si no tienes la fuente, quita esta línea o agrégala después
        primarySwatch: Colors.teal,
        useMaterial3: true,
      ),
      home: const HomeScreen(),
    );
  }
}

// ====================== PANTALLA PRINCIPAL CON BOTTOM NAV ======================
class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});
  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _selectedIndex = 0;

  final List<Widget> _screens = [
    const InicioTab(),
    const AvisosTab(),
    const GaleriaTab(),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: _screens[_selectedIndex],
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _selectedIndex,
        onTap: (i) => setState(() => _selectedIndex = i),
        selectedItemColor: Colors.teal.shade700,
        unselectedItemColor: Colors.grey,
        items: const [
          BottomNavigationBarItem(icon: Icon(Icons.home), label: "Inicio"),
          BottomNavigationBarItem(icon: Icon(Icons.notifications_active), label: "Avisos"),
          BottomNavigationBarItem(icon: Icon(Icons.photo_library), label: "Galería"),
        ],
      ),
    );
  }
}

// ====================== TAB INICIO ======================
class InicioTab extends StatefulWidget {
  const InicioTab({super.key});
  @override
  State<InicioTab> createState() => _InicioTabState();
}

class _InicioTabState extends State<InicioTab> {
  Map<String, dynamic> eventosCalendario = {};
  List<dynamic> proximosEventos = [];
  bool cargando = true;

  // CAMBIA ESTA URL POR TU DOMINIO REAL
  final String baseUrl = "https://juntavecinos.test/api.cl/api.php";

  @override
  void initState() {
    super.initState();
    cargarDatos();
  }

  Future<void> cargarDatos() async {
    try {
      final calRes = await http.get(Uri.parse("$baseUrl?action=eventos_calendario"));
      final proxRes = await http.get(Uri.parse("$baseUrl?action=proximos_eventos"));

      if (calRes.statusCode == 200) {
        setState(() => eventosCalendario = json.decode(calRes.body));
      }
      if (proxRes.statusCode == 200) {
        setState(() => proximosEventos = json.decode(proxRes.body));
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text("Error de conexión: $e")),
      );
    } finally {
      setState(() => cargando = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text("JJVV La Flecha", style: TextStyle(fontWeight: FontWeight.w900)),
        backgroundColor: Colors.teal.shade700,
        foregroundColor: Colors.white,
      ),
      body: cargando
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: cargarDatos,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  const Text("Calendario", style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: Colors.teal)),
                  const SizedBox(height: 16),
                  CalendarWidget(eventos: eventosCalendario),
                  const SizedBox(height: 32),
                  const Text("Próximos eventos", style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: Colors.teal)),
                  const SizedBox(height: 16),
                  ...proximosEventos.map((e) => EventoCard(evento: e)).toList(),
                  const SizedBox(height: 80),
                ],
              ),
            ),
    );
  }
}

// ====================== CALENDARIO ======================
class CalendarWidget extends StatefulWidget {
  final Map<String, dynamic> eventos;
  const CalendarWidget({required this.eventos, super.key});

  @override
  State<CalendarWidget> createState() => _CalendarWidgetState();
}

class _CalendarWidgetState extends State<CalendarWidget> {
  late DateTime mesActual = DateTime(DateTime.now().year, DateTime.now().month);

  void cambiarMes(int delta) => setState(() => mesActual = DateTime(mesActual.year, mesActual.month + delta));

  @override
  Widget build(BuildContext context) {
    final primerDiaMes = DateTime(mesActual.year, mesActual.month, 1);
    final diasEnMes = DateTime(mesActual.year, mesActual.month + 1, 0).day;
    final weekdayPrimerDia = primerDiaMes.weekday % 7; // domingo = 0

    return Card(
      elevation: 6,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                IconButton(onPressed: () => cambiarMes(-1), icon: const Icon(Icons.chevron_left)),
                Text(DateFormat('MMMM yyyy', 'es_ES').format(mesActual), style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
                IconButton(onPressed: () => cambiarMes(1), icon: const Icon(Icons.chevron_right)),
              ],
            ),
            const Divider(),
            GridView.count(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              crossAxisCount: 7,
              children: List.generate(42, (index) {
                final dia = index - weekdayPrimerDia + 1;
                if (dia < 1 || dia > diasEnMes) return const SizedBox.shrink();

                final fecha = DateTime(mesActual.year, mesActual.month, dia);
                final fechaStr = DateFormat('yyyy-MM-dd').format(fecha);
                final tieneEvento = widget.eventos.containsKey(fechaStr);

                return GestureDetector(
                  onTap: tieneEvento
                      ? () => showDialog(
                            context: context,
                            builder: (_) => AlertDialog(
                              title: Text(DateFormat('d MMMM yyyy', 'es_ES').format(fecha)),
                              content: Column(
                                mainAxisSize: MainAxisSize.min,
                                children: (widget.eventos[fechaStr] as List).map((e) => ListTile(
                                      title: Text(e['Nombre_evento']),
                                      subtitle: Text("${e['Hora_inicio']?.substring(0, 5)} - ${e['Hora_fin']?.substring(0, 5)}"),
                                    )).toList(),
                              ),
                            ),
                          )
                      : null,
                  child: Container(
                    margin: const EdgeInsets.all(4),
                    decoration: BoxDecoration(
                      color: tieneEvento ? Colors.teal.shade600 : Colors.grey.shade100,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Center(
                      child: Text(
                        "$dia",
                        style: TextStyle(
                          color: tieneEvento ? Colors.white : Colors.black,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ),
                );
              }),
            ),
          ],
        ),
      ),
    );
  }
}

class EventoCard extends StatelessWidget {
  final dynamic evento;
  const EventoCard({required this.evento, super.key});

  @override
  Widget build(BuildContext context) {
    final fecha = DateTime.parse(evento['Fecha_evento']);
    return Card(
      margin: const EdgeInsets.symmetric(vertical: 8),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: Colors.teal,
          child: Text(DateFormat('dd').format(fecha), style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
        ),
        title: Text(evento['Nombre_evento'], style: const TextStyle(fontWeight: FontWeight.bold)),
        subtitle: Text("${DateFormat('d MMMM yyyy', 'es_ES').format(fecha)} • ${evento['Hora_inicio']?.substring(0, 5)} - ${evento['Hora_fin']?.substring(0, 5)}"),
      ),
    );
  }
}

// ====================== TAB AVISOS ======================
class AvisosTab extends StatefulWidget {
  const AvisosTab({super.key});
  @override
  State<AvisosTab> createState() => _AvisosTabState();
}

class _AvisosTabState extends State<AvisosTab> {
  List<dynamic> avisos = [];
  bool cargando = true;
  final String baseUrl = "https://juntavecinos.test/api/api.php";

  @override
  void initState() {
    super.initState();
    cargarAvisos();
  }

  Future<void> cargarAvisos() async {
    final res = await http.get(Uri.parse("$baseUrl?action=avisos"));
    if (res.statusCode == 200) {
      setState(() {
        avisos = json.decode(res.body);
        cargando = false;
      });
    }
  }

  String mesEnEspanol(String fechaStr) {(String fechaStr) {
    final date = DateTime.parse(fechaStr);
    return DateFormat('MMMM yyyy', 'es_ES').format(date).toUpperCase();
  }

  @override
  Widget build(BuildContext context) {
    if (cargando) return const Scaffold(body: Center(child: CircularProgressIndicator()));

    return Scaffold(
      appBar: AppBar(title: const Text("Avisos e Historial"), backgroundColor: Colors.teal.shade700, foregroundColor: Colors.white),
      body: avisos.isEmpty
          ? const Center(child: Text("Aún no hay avisos", style: TextStyle(fontSize: 20)))
          : ListView.builder(
              padding: const EdgeInsets.all(16),
              itemBuilder: (context, i) {
                final a = avisos[i];
                final fecha = DateTime.parse(a['fecha']);
                final esEvento = a['tipo'] == 'evento';
                final esFeria = a['tipo_reserva'] == 'Feria artesanal';

                return Card(
                  color: esEvento ? Colors.green.shade50 : Colors.teal.shade50,
                  child: ListTile(
                    leading: CircleAvatar(
                      backgroundColor: esEvento ? Colors.green.shade700 : Colors.teal.shade700,
                      child: Text(DateFormat('dd').format(fecha), style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                    ),
                    title: Row(
                      children: [
                        Expanded(child: Text(a['titulo'], style: const TextStyle(fontWeight: FontWeight.bold))),
                        if (esFeria)
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                            decoration: BoxDecoration(color: Colors.orange.shade700, borderRadius: BorderRadius.circular(20)),
                            child: const Text("FERIA", style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                          ),
                      ],
                    ),
                    subtitle: a['detalle'] != null ? Text('"${a['detalle']}"', style: const TextStyle(fontStyle: FontStyle.italic)) : null,
                    trailing: Text("${a['Hora_inicio']?.substring(0, 5) ?? ''}${a['Hora_fin'] != null ? ' - ${a['Hora_fin'].substring(0, 5)}' : ''}"),
                    isThreeLine: true,
                  ),
                );
              },
              itemCount: avisos.length,
            ),
    );
  }
}

// ====================== TAB GALERÍA ======================
class GaleriaTab extends StatefulWidget {
  const GaleriaTab({super.key});
  @override
  State<GaleriaTab> createState() => _GaleriaTabState();
}

class _GaleriaTabState extends State<GaleriaTab> {
  List<dynamic> imagenes = [];
  final String baseUrl = "https://juntavecinos.test/api.cl";

  @override
  void initState() {
    super.initState();
    http.get(Uri.parse("$baseUrl/api.php?action=galeria")).then((res) {
      if (res.statusCode == 200) setState(() => imagenes = json.decode(res.body));
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text("Galería"), backgroundColor: Colors.teal.shade700, foregroundColor: Colors.white),
      body: imagenes.isEmpty
          ? const Center(child: CircularProgressIndicator())
          : GridView.builder(
              padding: const EdgeInsets.all(8),
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(crossAxisCount: 3, spacing: 4, mainAxisSpacing: 4),
              itemCount: imagenes.length,
              itemBuilder: (context, i) {
                final img = imagenes[i];
                final url = "$baseUrl/${img['ruta']}";
                return GestureDetector(
                  onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => FullScreenGallery(imagenes: imagenes, initialIndex: i))),
                  child: Hero(
                    tag: url,
                    child: CachedNetworkImage(imageUrl: url, fit: BoxFit.cover, placeholder: (_, __) => Container(color: Colors.grey.shade300)),
                  ),
                );
              },
            ),
    );
  }
}

class FullScreenGallery extends StatelessWidget {
  final List<dynamic> imagenes;
  final int initialIndex;
  const FullScreenGallery({required this.imagenes, required this.initialIndex, super.key});

  @override
  Widget build(BuildContext context) {
    final PageController controller = PageController(initialPage: initialIndex);
    final String baseUrl = "https://juntavecinos.test/api.cl";

    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(backgroundColor: Colors.black),
      body: PhotoViewGallery.builder(
        pageController: controller,
        itemCount: imagenes.length,
        builder: (context, i) {
          final url = "$baseUrl/${imagenes[i]['ruta']}";
          return PhotoViewGalleryPageOptions(
            imageProvider: NetworkImage(url),
            heroAttributes: PhotoViewHeroAttributes(tag: url),
          );
        },
      ),
    );
  }
}