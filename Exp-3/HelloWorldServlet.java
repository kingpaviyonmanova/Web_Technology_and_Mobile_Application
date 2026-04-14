import java.io.*;
import javax.servlet.*;
import javax.servlet.http.*;
public class HelloWorldServlet extends HttpServlet
{
 public void doGet(HttpServletRequest request, HttpServletResponse response)
 throws ServletException, IOException
 {
 response.setContentType("text/html");
 PrintWriter out = response.getWriter();
 out.println("<html>");
 out.println("<head><title>Hello World Servlet</title></head>");
 out.println("<body>");
 out.println("<h1>Hello, World!</h1>");
 out.println("</body>");
 out.println("</html>");
 }
}
