ENV HTTP_PROXY http://192.168.0.1:3128
ENV HTTPS_PROXY http://192.168.0.1:3128

RUN if [ ! -z "${HTTP_PROXY}" ]; then printf "Acquire {\nHTTP::proxy \"$HTTP_PROXY\";\nHTTPS::proxy \"$HTTPS_PROXY\";\n}\n"  > /etc/apt/apt.conf.d/10proxy.conf ; fi
RUN cat /etc/apt/apt.conf.d/10proxy.conf